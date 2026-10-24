<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Signs in as "ada@example.com" with the password "password", so a wrong
 * password followed by the right one shows the box asking for new tokens.
 */
#[Layout('layout')]
class SignIn extends Component
{
    use InteractsWithMosparo;

    public string $email = '';

    public string $password = '';

    public ?string $result = null;

    public function signIn(): void
    {
        $data = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! $this->mosparo->verify($data)) {
            $this->result = 'Stopped by mosparo.';

            return;
        }

        $this->result = $this->email === 'ada@example.com' && $this->password === 'password'
            ? 'Signed in.'
            : 'Wrong email or password - try again, the spam check starts over.';
    }

    public function render(): View
    {
        return view('livewire.sign-in');
    }
}
