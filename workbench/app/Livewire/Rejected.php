<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * A form mosparo turns away: with "Change the message on the server" ticked,
 * the message is altered before mosparo is asked, so it no longer matches what
 * the box checked. Unticked, the next attempt goes through.
 */
#[Layout('layout', ['title' => 'Rejected'])]
class Rejected extends Component
{
    use InteractsWithMosparo;

    public string $message = 'Hello';

    public bool $tamper = true;

    public ?string $result = null;

    public function send(): void
    {
        $data = $this->validate(['message' => ['required', 'string']]);

        if ($this->tamper) {
            $data['message'] .= ' - changed on the server';
        }

        $this->result = $this->mosparo->verify($data, fields: ['message'])
            ? 'Accepted by mosparo.'
            : null;
    }

    public function render(): View
    {
        return view('livewire.rejected');
    }
}
