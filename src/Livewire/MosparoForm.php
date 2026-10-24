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
     *
     * @param array<string, mixed> $tokens
     */
    public function update(array $tokens): void
    {
        $this->submitToken = is_string($tokens['submitToken'] ?? null) ? $tokens['submitToken'] : '';
        $this->validationToken = is_string($tokens['validationToken'] ?? null) ? $tokens['validationToken'] : '';
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
     * Checks the submission with mosparo. Once mosparo has seen the tokens they
     * are spent, whatever the answer: they are cleared, and the boxes in this
     * component fetch new ones. Validation errors on the form come first, and
     * leave the tokens as they are.
     *
     * @param array<string, mixed>|Form $data The submitted fields, keyed like the form's inputs.
     * @param string|null $project The project the form's box uses; the default when null.
     */
    public function verify(array|Form $data, ?string $project = null): bool
    {
        $mosparo = app(Mosparo::class)->project($project);

        if (! $mosparo->enabled()) {
            return true;
        }

        $this->validate();

        if ($data instanceof Form) {
            $data = $data->validate();
        }

        $passes = $mosparo->passes($data, $this->submitToken, $this->validationToken);

        $this->reset();
        $this->getComponent()->dispatch(Mosparo::RESET_EVENT, id: $this->getComponent()->getId());

        if (! $passes) {
            $this->addError('submitToken', __('mosparo::messages.failed'));
        }

        return $passes;
    }
}
