# Livewire

Use the `InteractsWithMosparo` trait, and check before acting:

```php
use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Livewire\Component;

class SignIn extends Component
{
    use InteractsWithMosparo;

    public SignInForm $form;

    public function signIn(): void
    {
        if (! $this->mosparo->verify($this->form, fields: ['email'])) {
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
    @mosparoError <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Sign in</button>
</form>
```

The trait gives the component `$mosparo`, which holds the tokens the box sends after its check, and the listener that receives them.

## What verify() does

`$this->mosparo->verify($data, $project = null, $fields = [])`:

1. Validates `$data` when it is a Livewire `Form`. A form failing validation throws as usual, and the tokens are kept for the next try. `$data` can also be an array of fields you have already validated.
2. Returns `true` when mosparo is [disabled](switching-off.md).
3. Checks the submission with mosparo. Missing tokens and a failed check both put the package's message on `mosparo.submitToken`, which `@mosparoError` shows.

Once mosparo has seen the tokens, they are spent, whatever the answer. They are cleared, and the boxes in this component fetch new ones, so a form that failed for another reason, such as a wrong password, can be sent again.

`fields` works as for [plain forms](plain-forms.md#fields-mosparo-must-check): the check fails unless mosparo checked each listed field and found it valid.

## Which fields mosparo sees

mosparo compares what the box saw with what the server sends, field by field. Give every input a `name` matching its key in the data you verify, such as `name="email"` for `$form->email`. Nested data matches bracketed names: `name="address[street]"` for `$form->address['street']`.

## More than one form

Each Livewire component has one `$mosparo`, so give each protected form its own component. Two boxes in one component share the tokens.
