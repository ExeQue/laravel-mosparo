<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A comment form on the "visible" project, with the checkbox shown or, with
 * $forceInvisible, hidden by mosparo's forceInvisible option.
 */
class Comment extends Component
{
    use InteractsWithMosparo;

    #[Locked]
    public bool $forceInvisible = false;

    public string $comment = '';

    public ?string $result = null;

    public function send(): void
    {
        $data = $this->validate(['comment' => ['required', 'string']]);

        $this->result = $this->mosparo->verify($data, 'visible', fields: ['comment']) ? 'Accepted by mosparo.' : null;
    }

    public function render(): View
    {
        return view('livewire.comment');
    }
}
