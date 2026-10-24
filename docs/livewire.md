<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Livewire

Use the `InteractsWithMosparo` trait and check before acting:

```php
use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Livewire\Component;

class SignIn extends Component
{
    use InteractsWithMosparo;

    public SignInForm $form;

    public function signIn(): void
    {
        if (! $this->mosparo->verify($this->form)) {
            return;
        }

        // ...
    }
}
```

```blade
<form wire:submit="signIn">
    <input name="email" wire:model="form.email">
    <input name="password" type="password" wire:model="form.password">

    @mosparo
    @error('mosparo.submitToken') <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Sign in</button>
</form>
```

The trait adds two things to the component:

- `$mosparo`, a `MosparoForm` holding the tokens the box hands over.
- `updateMosparo()`, which the box calls after its check.

`verify()` takes a Livewire `Form` - it validates it first - or an array of already validated fields. Pass the project as the second argument when the box uses another one: `$this->mosparo->verify($this->form, 'newsletter')`.

When the form fails validation, mosparo is not asked and the tokens are kept. Once mosparo has been asked, the tokens are spent: they are cleared, and the box on that component fetches new ones, whatever the answer was.

## Which fields mosparo sees

mosparo compares what the box saw with what the server sends, field by field. Give every input a `name` matching its key in the data you verify, as `name="email"` for `$form->email` above. The box leaves out passwords, hidden fields, checkboxes, radio buttons and files on its own.
