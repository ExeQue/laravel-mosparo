<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Livewire;

use ExeQue\Mosparo\Mosparo;
use Livewire\Form;

/**
 * The tokens of a box in a Livewire component, and the check that spends them.
 */
class MosparoForm extends Form
{
    public string $submitToken = '';

    public string $validationToken = '';

    /**
     * Takes the tokens the box hands over after its check.
     */
    public function update(mixed $submitToken, mixed $validationToken): void
    {
        $this->submitToken = is_string($submitToken) ? $submitToken : '';
        $this->validationToken = is_string($validationToken) ? $validationToken : '';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'submitToken' => ['required', 'string'],
            'validationToken' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'submitToken.required' => __('mosparo::messages.failed'),
            'validationToken.required' => __('mosparo::messages.failed'),
        ];
    }

    /**
     * Validates the form, then checks the submission with mosparo. Returns
     * false when mosparo turns it away; stop there.
     *
     * A form failing validation throws as usual and leaves the tokens alone.
     * Once mosparo has seen the tokens they are spent, whatever the answer:
     * they are cleared, and the boxes in this component fetch new ones.
     *
     * @param array<string, mixed>|Form $data The form, or the submitted fields keyed like the form's inputs.
     * @param list<string> $fields Fields that must have been part of mosparo's check.
     * @param string|null $project The project the form's box uses; the default when null.
     * @param array<string, mixed> $metadata Stored with the submission in mosparo when it passes.
     */
    public function verify(array|Form $data, array $fields, ?string $project = null, array $metadata = []): bool
    {
        if ($data instanceof Form) {
            $data->validate();
            $data = $data->all();
        }

        $mosparo = app(Mosparo::class)->project($project);

        if (! $mosparo->enabled()) {
            return true;
        }

        $this->validate();

        $passes = $mosparo->passes($data, $this->submitToken, $this->validationToken, $fields);

        if ($passes && $metadata !== []) {
            rescue(fn (): bool => $mosparo->storeMetadata($this->submitToken, $this->validationToken, $metadata));
        }

        $this->reset();
        $this->getComponent()->dispatch(Mosparo::RESET_EVENT, id: $this->getComponent()->getId());

        if (! $passes) {
            $this->addError('submitToken', __('mosparo::messages.failed'));
        }

        return $passes;
    }
}
