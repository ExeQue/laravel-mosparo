<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Rules;

use Closure;
use ExeQue\Mosparo\Mosparo;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Checks a plain form post with mosparo: the submitted fields, with the two
 * tokens the box placed in the form. Put it on `_mosparo_submitToken`.
 *
 *     '_mosparo_submitToken' => [new ValidMosparo],
 *     '_mosparo_submitToken' => [new ValidMosparo('newsletter')],
 */
class ValidMosparo implements DataAwareRule, ValidationRule
{
    /**
     * Runs when the token is missing too, so a form posted without the box fails.
     */
    public bool $implicit = true;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly ?string $project = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $passes = app(Mosparo::class)->project($this->project)->passes(
            $this->scalars($this->data),
            $this->string($this->data['_mosparo_submitToken'] ?? null),
            $this->string($this->data['_mosparo_validationToken'] ?? null),
        );

        if ($passes) {
            return;
        }

        $fail('mosparo::messages.failed')->translate();
    }

    /**
     * The submission without files and other objects, which the box leaves
     * out of its check as well.
     *
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    private function scalars(array $data): array
    {
        $kept = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $kept[$key] = $this->scalars($value);

                continue;
            }

            if (is_object($value)) {
                continue;
            }

            $kept[$key] = $value;
        }

        return $kept;
    }

    private function string(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
