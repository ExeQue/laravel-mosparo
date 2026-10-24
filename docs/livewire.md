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
        if (! $this->mosparo->verify($this->form, ['email'])) {
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

`$this->mosparo->verify($data, $fields, $project = null)` returns `false` when the check fails - stop there. Step by step:

1. Validates `$data` when it is a Livewire `Form`. A form failing validation throws as usual, and the tokens are kept for the next try. `$data` can also be an array of fields you have already validated.
2. Returns `true` when mosparo is [disabled](switching-off.md).
3. Checks the submission with mosparo. Missing tokens and a failed check both put the package's message on `mosparo.submitToken`, which `@mosparoError` shows.

Once mosparo has seen the tokens, they are spent, whatever the answer. They are cleared, and the boxes in this component fetch new ones for the next submission.

Pass `metadata:` to have mosparo keep it with the submission when the check passes. See [Reading data](data.md#metadata).

`$fields` works as for [plain forms](plain-forms.md#fields-mosparo-must-check): the check fails unless mosparo checked each listed field and found it valid.

Give the Form string properties for the fields mosparo checks. The browser sends text, and a float or boolean property compares as something else.

## Which fields mosparo sees

mosparo compares what the box saw with what the server sends, field by field. Give every input a `name` matching its key in the data you verify, such as `name="email"` for `$form->email`. Nested data matches bracketed names: `name="address[street]"` for `$form->address['street']`.

## Sending the form

The box checks the form when it is submitted, so send it with `wire:submit` on the `<form>`. An action called by `wire:click` on a plain button skips the box's check, and `verify()` then fails for lack of tokens.

## More than one form

Each Livewire component has one `$mosparo`, so give each protected form its own component. The box hands its tokens to the component it is rendered in: put the trait on that component, not on a parent.
