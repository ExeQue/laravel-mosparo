<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use Illuminate\Support\HtmlString;
use Mosparo\ApiClient\Client;
use Mosparo\ApiClient\Exception;
use Mosparo\ApiClient\VerificationResult;

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
        private readonly bool $switchedOn,
    ) {
    }

    public function enabled(): bool
    {
        if (! $this->switchedOn) {
            return false;
        }

        return filled($this->host())
            && filled($this->uuid())
            && filled($this->publicKey())
            && filled($this->privateKey());
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
     * Whether mosparo lets the submission through. Passes when switched off,
     * and fails closed when a token is missing or mosparo cannot be reached.
     *
     * @param array<array-key, mixed> $formData The submitted fields, keyed like the form's inputs.
     */
    public function passes(array $formData, ?string $submitToken, ?string $validationToken): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (blank($submitToken) || blank($validationToken)) {
            return false;
        }

        try {
            return $this->verify($formData, $submitToken, $validationToken)->isSubmittable();
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * Asks mosparo about a submission. Each pair of tokens can be verified once.
     *
     * @param array<array-key, mixed> $formData
     *
     * @throws Exception When mosparo cannot be reached or answers nonsense.
     */
    public function verify(array $formData, string $submitToken, string $validationToken): VerificationResult
    {
        return $this->client()->verifySubmission($formData, $submitToken, $validationToken);
    }

    public function client(): Client
    {
        return new Client(
            $this->host(),
            $this->publicKey(),
            $this->privateKey(),
            $this->config['client_arguments'] ?? [],
        );
    }

    /**
     * The project's stylesheet and mosparo's frontend script, for the page head.
     */
    public function script(): HtmlString
    {
        if (! $this->enabled()) {
            return new HtmlString('');
        }

        return new HtmlString(sprintf(
            '<link href="%1$s/resources/%2$s.css" rel="stylesheet">'."\n".'<script src="%1$s/build/mosparo-frontend.js" defer></script>',
            e($this->host()),
            e($this->uuid()),
        ));
    }

    private function privateKey(): string
    {
        return (string) ($this->config['private_key'] ?? '');
    }
}
