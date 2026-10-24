<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Livewire\Concerns;

use ExeQue\Mosparo\Livewire\MosparoForm;

/**
 * Gives a Livewire component the tokens of its box as `$mosparo`, and the
 * `updateMosparo()` method the box calls after its check. Check a submission
 * with `$this->mosparo->verify($this->form)`.
 */
trait InteractsWithMosparo
{
    public MosparoForm $mosparo;

    /**
     * Called by the box with the submit and validation token of its check.
     *
     * @param array<string, mixed> $tokens
     */
    public function updateMosparo(array $tokens): void
    {
        $this->mosparo->update($tokens);
    }
}
