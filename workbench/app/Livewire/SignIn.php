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
#[Layout('layout', ['title' => 'Livewire'])]
class SignIn extends Component
{
    use InteractsWithMosparo;

    public string $email = '';

    public string $password = '';

    public ?string $result = null;

    public bool $signedIn = false;

    public function signIn(): void
    {
        $data = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $this->result = null;

        if (! $this->mosparo->verify($data, ['email'])) {
            return;
        }

        $this->signedIn = $this->email === 'ada@example.com' && $this->password === 'password';

        $this->result = $this->signedIn
            ? 'Signed in.'
            : 'Wrong email or password. Try again - the box is ready for another check.';
    }

    public function render(): View
    {
        return view('livewire.sign-in');
    }
}
