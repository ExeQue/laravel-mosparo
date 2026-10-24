<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use Closure;
use DateTime;
use DateTimeInterface;
use ExeQue\Mosparo\Cache\ProjectCache;
use ExeQue\Mosparo\Data\RulePackageImport;
use ExeQue\Mosparo\Data\RulePackageRuleItems;
use ExeQue\Mosparo\Data\RulePackageRules;
use ExeQue\Mosparo\Data\RulePackageUpdate;
use ExeQue\Mosparo\Data\Statistics;
use ExeQue\Mosparo\Data\Verification;
use ExeQue\Mosparo\Enums\FieldStatus;
use ExeQue\Mosparo\Exceptions\ConnectionFailed;
use ExeQue\Mosparo\Exceptions\IncompleteProject;
use ExeQue\Mosparo\Exceptions\MosparoException;
use ExeQue\Mosparo\Exceptions\RequestFailed;
use ExeQue\Mosparo\Support\FormData;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception as ClientException;
use SplFileObject;
use Throwable;

/**
 * One mosparo project: where it lives, its keys, and the checks against it.
 */
class Project
{
    /**
     * @param array{host?: ?string, uuid?: ?string, public_key?: ?string, private_key?: ?string, client_arguments?: array<string, mixed>} $config
     */
    public function __construct(
        public readonly string $name,
        private readonly array $config,
        private readonly bool $enabled,
        private readonly ProjectCache $cache,
    ) {
    }

    /**
     * The same project, reading from mosparo every time.
     */
    public function withoutCache(): static
    {
        return new static($this->name, $this->config, $this->enabled, $this->cache->with(false));
    }

    /**
     * The same project, caching what it reads, for $ttl seconds when given.
     */
    public function withCache(?int $ttl = null): static
    {
        return new static($this->name, $this->config, $this->enabled, $this->cache->with(true, $ttl));
    }

    /**
     * Clears what this project cached.
     */
    public function flushCache(): void
    {
        $this->cache->flush();
    }

    /**
     * Whether mosparo is enabled. Disabled, every check passes and nothing
     * is rendered.
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function host(): string
    {
        return rtrim((string) ($this->config['host'] ?? ''), '/');
    }

    public function uuid(): string
    {
        return (string) ($this->config['uuid'] ?? '');
    }

    public function publicKey(): string
    {
        return (string) ($this->config['public_key'] ?? '');
    }

    /**
     * Whether mosparo lets the submission through. Passes while disabled.
     * Fails when a token is missing, when mosparo cannot be reached (the
     * exception is reported), and when one of $fields was not checked by
     * mosparo and found valid.
     *
     * @param array<array-key, mixed> $formData The submitted fields, keyed like the form's inputs.
     * @param list<string> $fields Fields that must have been part of mosparo's check, such as `message`.
     *                             A field kept out of the box's check is otherwise not noticed.
     */
    public function passes(array $formData, mixed $submitToken, mixed $validationToken, array $fields): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! is_string($submitToken) || $submitToken === '' || ! is_string($validationToken) || $validationToken === '') {
            return false;
        }

        try {
            $result = $this->verify($formData, $submitToken, $validationToken);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        if (! $result->submittable) {
            return false;
        }

        foreach ($fields as $field) {
            if ($result->field($field) !== FieldStatus::Valid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Asks mosparo about a submission. Each pair of tokens can be verified once.
     *
     * @param array<array-key, mixed> $formData The submitted fields, keyed like the form's inputs.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses or answers something unexpected.
     * @throws IncompleteProject When the project misses its host, uuid or a key, or its host has a path.
     */
    public function verify(array $formData, string $submitToken, string $validationToken): Verification
    {
        return $this->call(fn (Client $client): Verification => Verification::from(
            $client->verifySubmission(FormData::prepare($formData), $submitToken, $validationToken),
        ));
    }

    /**
     * Stores metadata with a submission mosparo has verified, using the same
     * tokens. Call it after the check.
     *
     * @param array<string, mixed> $metadata
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses the metadata.
     */
    public function storeMetadata(string $submitToken, string $validationToken, array $metadata): bool
    {
        return $this->call(fn (Client $client): bool => $client->storeMetadata($submitToken, $validationToken, $metadata));
    }

    /**
     * Submissions per day: valid and spam, in total and by date. Cached when
     * caching is on.
     *
     * @param int $days The number of days back; every day mosparo keeps when 0.
     * @param DateTimeInterface|null $from The first day, instead of counting back.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses.
     */
    public function statistics(int $days = 0, ?DateTimeInterface $from = null): Statistics
    {
        return $this->cache->remember(
            "statistics:{$days}:{$from?->format('Y-m-d')}",
            fn (): Statistics => $this->call(fn (Client $client): Statistics => Statistics::from(
                $client->getStatisticByDate($days, $from === null ? null : DateTime::createFromInterface($from)),
            )),
        );
    }

    /**
     * One page of the rules in a rule package. Cached when caching is on.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses.
     */
    public function rulePackageRules(int $rulePackageId, int $page = 1, int $perPage = 1000): RulePackageRules
    {
        return $this->cache->remember(
            "rule-package:{$rulePackageId}:rules:{$page}:{$perPage}",
            fn (): RulePackageRules => $this->call(fn (Client $client): RulePackageRules => RulePackageRules::from(
                $client->getRulePackageRules($rulePackageId, $page, $perPage),
            )),
        );
    }

    /**
     * One page of the items of a rule in a rule package. Cached when caching is on.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses.
     */
    public function rulePackageRuleItems(int $rulePackageId, int $ruleId, int $page = 1, int $perPage = 1000): RulePackageRuleItems
    {
        return $this->cache->remember(
            "rule-package:{$rulePackageId}:rules:{$ruleId}:items:{$page}:{$perPage}",
            fn (): RulePackageRuleItems => $this->call(fn (Client $client): RulePackageRuleItems => RulePackageRuleItems::from(
                $client->getRulePackageRulesRuleItems($rulePackageId, $ruleId, $page, $perPage),
            )),
        );
    }

    /**
     * Streams the hash index of a rule package into $target. Never cached.
     *
     * @param callable(int $bytes): void|null $progress Called as the index arrives.
     *
     * @return bool False when mosparo has no index for the package.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses.
     */
    public function streamRulePackageHashIndex(int $rulePackageId, SplFileObject $target, ?callable $progress = null): bool
    {
        return $this->call(fn (Client $client): bool => $client->streamRulePackageHashIndex(
            $rulePackageId,
            $target,
            $progress ?? static function (): void {
            },
        ));
    }

    /**
     * Replaces the content of a rule package, and clears this project's cache.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses the package.
     */
    public function importRulePackage(int $rulePackageId, string $content, string $hash): RulePackageImport
    {
        try {
            return $this->call(fn (Client $client): RulePackageImport => RulePackageImport::from(
                $client->storeRulePackage($rulePackageId, $content, $hash),
            ));
        } finally {
            $this->cache->flush();
        }
    }

    /**
     * Applies a batch of changes to a rule package, and clears this project's cache.
     *
     * @param list<array<string, mixed>> $tasks The changes, as mosparo's rule package API takes them.
     *
     * @throws ConnectionFailed When mosparo cannot be reached.
     * @throws RequestFailed When mosparo refuses the changes.
     */
    public function updateRulePackage(int $rulePackageId, array $tasks): RulePackageUpdate
    {
        try {
            return $this->call(fn (Client $client): RulePackageUpdate => RulePackageUpdate::from(
                $client->batchUpdateRulePackage($rulePackageId, $tasks),
            ));
        } finally {
            $this->cache->flush();
        }
    }

    /**
     * mosparo's API client for this project.
     *
     * @throws IncompleteProject When the project misses its host, uuid or a key, or its host has a path.
     */
    public function client(): Client
    {
        $this->ensureComplete();

        return new Client(
            $this->host(),
            $this->publicKey(),
            $this->privateKey(),
            $this->config['client_arguments'] ?? [],
        );
    }

    /**
     * What the box needs to start: where mosparo is, the project, and options
     * for mosparo's frontend.
     *
     * @param array<string, mixed> $options
     *
     * @return array{host: string, uuid: string, publicKey: string, options: array<string, mixed>|object}
     *
     * @throws IncompleteProject When the project misses its host, uuid or a key.
     */
    public function frontend(array $options = []): array
    {
        $this->ensureComplete();

        return [
            'host' => $this->host(),
            'uuid' => $this->uuid(),
            'publicKey' => $this->publicKey(),
            'options' => (object) $options,
        ];
    }

    /**
     * mosparo's frontend script, and the script that starts every box on the
     * page, for the page head. Empty while disabled.
     *
     * @throws IncompleteProject When the project misses its host, uuid or a key.
     */
    public function script(): HtmlString
    {
        if (! $this->enabled) {
            return new HtmlString('');
        }

        $this->ensureComplete();

        $nonce = Vite::cspNonce() === null ? '' : ' nonce="'.e(Vite::cspNonce()).'"';

        return new HtmlString(sprintf(
            '<script%1$s>%2$s</script>'."\n".'<script%1$s src="%3$s/build/mosparo-frontend.js" defer></script>',
            $nonce,
            self::boxes(),
            e($this->host()),
        ));
    }

    /**
     * Runs a call on mosparo's API client, turning its failures into this
     * package's exceptions.
     *
     * @template T
     *
     * @param Closure(Client): T $call
     *
     * @return T
     *
     * @throws MosparoException
     */
    private function call(Closure $call): mixed
    {
        $client = $this->client();

        try {
            return $call($client);
        } catch (ClientException $exception) {
            throw $exception->getPrevious() instanceof GuzzleException
                ? ConnectionFailed::to($this->name, $exception)
                : RequestFailed::refused($this->name, $exception);
        } catch (Throwable $exception) {
            throw RequestFailed::unexpected($this->name, $exception);
        }
    }

    private function privateKey(): string
    {
        return (string) ($this->config['private_key'] ?? '');
    }

    /**
     * @throws IncompleteProject
     */
    private function ensureComplete(): void
    {
        $missing = array_keys(array_filter([
            'host' => $this->host(),
            'uuid' => $this->uuid(),
            'public key' => $this->publicKey(),
            'private key' => $this->privateKey(),
        ], fn (string $value): bool => $value === ''));

        if ($missing !== []) {
            throw IncompleteProject::missing($this->name, $missing);
        }

        // mosparo's API client puts its paths at the root of the host.
        if (trim((string) parse_url($this->host(), PHP_URL_PATH), '/') !== '') {
            throw IncompleteProject::hostWithPath($this->name, $this->host());
        }
    }

    private static function boxes(): string
    {
        static $script;

        return $script ??= trim((string) file_get_contents(__DIR__.'/../resources/js/boxes.js'));
    }
}
