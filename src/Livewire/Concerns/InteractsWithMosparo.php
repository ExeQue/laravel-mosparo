<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Livewire\Concerns;

use ExeQue\Mosparo\Livewire\MosparoForm;
use ExeQue\Mosparo\Mosparo;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

/**
 * Gives a Livewire component the tokens of its box as `$mosparo`. Check a
 * submission, and stop when it fails:
 *
 *     if (! $this->mosparo->verify($this->form, ['message'])) {
 *         return;
 *     }
 */
trait InteractsWithMosparo
{
    public MosparoForm $mosparo;

    /**
     * Receives the tokens the box dispatches after its check.
     */
    #[On(Mosparo::CHECKED_EVENT)]
    #[Renderless]
    public function updateMosparo(mixed $submitToken = null, mixed $validationToken = null): void
    {
        $this->mosparo->update($submitToken, $validationToken);
    }
}
