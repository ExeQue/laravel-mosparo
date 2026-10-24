<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Rules;

use Closure;
use ExeQue\Mosparo\Mosparo;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Checks a plain form post with mosparo: the submitted fields, with the two
 * tokens the box placed in the form. Put it on `_mosparo_submitToken`:
 *
 *     '_mosparo_submitToken' => [new ValidMosparo(fields: ['email'])],
 *     '_mosparo_submitToken' => [new ValidMosparo('newsletter', fields: ['email'])],
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

    /**
     * @param string|null $project The project the form's box uses; the default when null.
     * @param list<string> $fields Fields that must have been part of mosparo's check.
     */
    public function __construct(
        private readonly ?string $project = null,
        private readonly array $fields = [],
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
            $this->data,
            $this->data['_mosparo_submitToken'] ?? null,
            $this->data['_mosparo_validationToken'] ?? null,
            $this->fields,
        );

        if (! $passes) {
            $fail('mosparo::messages.failed')->translate();
        }
    }
}
