<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Rules;

use Closure;
use ExeQue\Mosparo\Mosparo;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Checks a submission with mosparo: the fields of an object, with the two
 * tokens the box placed among them. Put it on that object, with the fields
 * mosparo must have checked:
 *
 *     'input' => [ValidMosparo::make(['email', 'message'])],
 *     // input: {email: "…", message: "…", _mosparo_submitToken: "…", _mosparo_validationToken: "…"}
 *
 * A plain form post has no object around its fields, so the rule goes on its
 * submit token instead, and checks the fields next to it:
 *
 *     '_mosparo_submitToken' => [ValidMosparo::make(['email'])],
 *
 * The tokens are found under the names mosparo's box gives them, unless the
 * object names them otherwise:
 *
 *     'input' => [ValidMosparo::make(['email'])->tokens('mosparo_submit_token', 'mosparo_validation_token')],
 *
 * A failed check is reported on the submit token, so `input.mosparo_submit_token`
 * above and `_mosparo_submitToken` on a plain form post.
 */
class ValidMosparo implements DataAwareRule, ValidationRule, ValidatorAwareRule
{
    /**
     * Runs when the tokens are missing too, so a form posted without the box fails.
     */
    public bool $implicit = true;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private Validator $validator;

    private string $submitTokenKey = '_mosparo_submitToken';

    private string $validationTokenKey = '_mosparo_validationToken';

    /**
     * @param list<string> $fields Fields that must have been part of mosparo's check.
     * @param string|null $project The project the form's box uses; the default when null.
     */
    public function __construct(
        private readonly array $fields = [],
        private readonly ?string $project = null,
    ) {
    }

    /**
     * @param list<string> $fields Fields that must have been part of mosparo's check.
     * @param string|null $project The project the form's box uses; the default when null.
     */
    public static function make(array $fields = [], ?string $project = null): static
    {
        return new static($fields, $project);
    }

    /**
     * The names the two tokens have in the submission, instead of the
     * `_mosparo_submitToken` and `_mosparo_validationToken` of mosparo's box.
     */
    public function tokens(string $submitToken, string $validationToken): static
    {
        $this->submitTokenKey = $submitToken;
        $this->validationTokenKey = $validationToken;

        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function setValidator(ValidatorContract $validator): static
    {
        /** @var Validator $validator Laravel's own, which runs the rule. */
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $onObject = $attribute !== $this->submitTokenKey && ! Str::endsWith($attribute, '.'.$this->submitTokenKey);
        $submission = (array) ($onObject ? $value : $this->parentOf($attribute));

        $passes = app(Mosparo::class)->project($this->project)->passes(
            Arr::except($submission, [$this->submitTokenKey, $this->validationTokenKey]),
            $submission[$this->submitTokenKey] ?? null,
            $submission[$this->validationTokenKey] ?? null,
            $this->fields,
        );

        if ($passes) {
            return;
        }

        if (! $onObject) {
            $fail('mosparo::messages.failed')->translate();

            return;
        }

        $this->failOn("{$attribute}.{$this->submitTokenKey}");
    }

    /**
     * The fields the submit token sits among: the whole data at the top.
     *
     * @return array<array-key, mixed>
     */
    private function parentOf(string $attribute): array
    {
        return str_contains($attribute, '.')
            ? (array) Arr::get($this->data, Str::beforeLast($attribute, '.'))
            : $this->data;
    }

    /**
     * Reports the failure on the submit token rather than on the object, with
     * a message given for that key in the validator first.
     */
    private function failOn(string $key): void
    {
        $this->validator->errors()->add(
            $key,
            $this->validator->customMessages[$key] ?? $this->validator->getTranslator()->get('mosparo::messages.failed'),
        );
    }
}
