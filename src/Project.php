<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use ExeQue\Mosparo\Exceptions\IncompleteProject;
use ExeQue\Mosparo\Support\FormData;
use Illuminate\Support\HtmlString;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception;
use Mosparo\ApiClient\VerificationResult;
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
    ) {
    }

    /**
     * Whether mosparo is switched on. Switched off, every check passes and
     * nothing is rendered.
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
     * Whether mosparo lets the submission through. Passes while switched off.
     * Fails when a token is missing, when mosparo cannot be reached (the
     * exception is reported), and when one of $fields was not checked by
     * mosparo and found valid.
     *
     * @param array<array-key, mixed> $formData The submitted fields, keyed like the form's inputs.
     * @param list<string> $fields Fields that must have been part of mosparo's check, such as `message`.
     */
    public function passes(array $formData, mixed $submitToken, mixed $validationToken, array $fields = []): bool
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

        if (! $result->isSubmittable()) {
            return false;
        }

        foreach ($fields as $field) {
            if ($result->getVerifiedField($field) !== VerificationResult::FIELD_VALID) {
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
     * @throws Exception When mosparo cannot be reached or answers nonsense.
     * @throws IncompleteProject When the project misses its host, uuid or a key.
     */
    public function verify(array $formData, string $submitToken, string $validationToken): VerificationResult
    {
        return $this->client()->verifySubmission(FormData::prepare($formData), $submitToken, $validationToken);
    }

    /**
     * mosparo's API client for this project.
     *
     * @throws IncompleteProject When the project misses its host, uuid or a key.
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
     * page, for the page head. Empty while switched off.
     *
     * @throws IncompleteProject When the project misses its host, uuid or a key.
     */
    public function script(): HtmlString
    {
        if (! $this->enabled) {
            return new HtmlString('');
        }

        $this->ensureComplete();

        return new HtmlString(sprintf(
            '<script>%s</script>'."\n".'<script src="%s/build/mosparo-frontend.js" defer onload="window.laravelMosparo.start()"></script>',
            self::boxes(),
            e($this->host()),
        ));
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
    }

    private static function boxes(): string
    {
        static $script;

        return $script ??= trim((string) file_get_contents(__DIR__.'/../resources/js/boxes.js'));
    }
}
