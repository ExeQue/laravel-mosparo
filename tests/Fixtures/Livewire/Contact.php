<?php

declare(strict_types=1);

namespace Tests\Fixtures\Livewire;

use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Livewire\Component;

class Contact extends Component
{
    use InteractsWithMosparo;

    public ContactForm $form;

    public bool $sent = false;

    public function send(): void
    {
        if (! $this->mosparo->verify($this->form, [])) {
            return;
        }

        $this->sent = true;
    }

    public function sendArray(): void
    {
        $this->sent = $this->mosparo->verify($this->form->validate(), []);
    }

    public function sendWithMetadata(): void
    {
        $this->sent = $this->mosparo->verify($this->form, [], metadata: ['source' => 'contact']);
    }

    public function sendMessage(): void
    {
        $this->sent = $this->mosparo->verify($this->form, ['message']);
    }

    public function render(): string
    {
        return <<<'BLADE'
            <form wire:submit="send">
                <input name="email" wire:model="form.email">
                <textarea name="message" wire:model="form.message"></textarea>
                @mosparo
                @mosparoError <p>{{ $message }}</p> @enderror
            </form>
            BLADE;
    }
}
