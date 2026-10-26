---
name: laravel-mosparo
description: Protect Laravel and Livewire forms with mosparo spam protection, read mosparo's statistics and rule packages, and test forms with Mosparo::fake(). Activate when adding, changing or testing a form with a mosparo box, or when working with @mosparo, @mosparoScript, @mosparoError, <x-mosparo />, ValidMosparo, InteractsWithMosparo, MosparoForm or the Mosparo facade.
---

# Laravel mosparo

## When to use this skill
Use it when a form needs mosparo's spam check, when a form that has one changes, and when testing such a form. The package is `exeque/laravel-mosparo`; its documentation is at https://exeque.github.io/laravel-mosparo/.

## How it works, in short
- `@mosparoScript` in the page head loads mosparo's frontend and the script that starts every box.
- `@mosparo` or `<x-mosparo />` inside a `<form>` puts the box there. On submit, the box sends the form's fields to mosparo, which hands back two tokens.
- The server checks the submission with those tokens: `ValidMosparo` on the object that holds the fields and the tokens in a JSON or GraphQL input (`'input' => [ValidMosparo::make(['email'])]`, token names changed with `->tokens(...)` or a set from `token_sets` with `->for('api')`), or on `_mosparo_submitToken` for a plain form post, `$this->mosparo->verify()` in a Livewire component, `Mosparo::passes()` anywhere else.
- A pair of tokens can be checked once. In Livewire, the box fetches new ones after every check by itself.

## Rules
1. **Name the fields mosparo must have checked.** Every check takes a list of fields: the ones a person types into, such as `['email', 'message']`. Without them, a field kept out of the box's check slips through. Passwords, hidden fields, checkboxes, radio buttons and files are never checked by mosparo, so never list them. `[]` checks the tokens alone and should be a deliberate choice.
2. **Inputs need a `name` that matches the data.** mosparo compares fields by name. `name="email"` for `$form->email`; nested data uses brackets: `name="address[street]"`, `name="rows[0][name]"`. A dotted name such as `content.general.content` never matches.
3. **The box goes inside the `<form>`**, and `@mosparoScript` goes in the head of every page with a box.
4. **Livewire: one box per component, and the trait on that component.** The box hands its tokens to the component it renders in. Submit with `wire:submit` on the form; an action called by `wire:click` on a plain button skips the box's check.
5. **Stop when the check fails.** `verify()` returns `false` and puts the message on `mosparo.submitToken`; return early.
6. **Show the error with `@mosparoError`**, which closes with Laravel's own `@enderror` and finds the error in Livewire and plain forms alike.
7. **Do not send passwords.** In Livewire, pass the validated fields without them, as mosparo never checks a password field anyway.

## Plain form
```php
use ExeQue\Mosparo\Rules\ValidMosparo;

public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        '_mosparo_submitToken' => [ValidMosparo::make(['email'])],
    ];
}
```

```blade
<form method="post" action="/newsletter">
    @csrf
    <input name="email" type="email">
    @mosparo
    @mosparoError <p class="error">{{ $message }}</p> @enderror
    <button type="submit">Subscribe</button>
</form>
```

## Livewire
```php
use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;
use Illuminate\Support\Arr;
use Livewire\Component;

class SignIn extends Component
{
    use InteractsWithMosparo;

    public SignInForm $form;

    public function signIn(): void
    {
        if (! $this->mosparo->verify(Arr::except($this->form->validate(), ['password']), ['email'])) {
            return;
        }

        // ...
    }
}
```

`verify($data, $fields, $project = null, $metadata = [])` takes a Livewire `Form`, which it validates first, or an array of validated fields. A form failing validation throws as usual and keeps the tokens. `metadata:` is stored with the submission in mosparo when it passes.

Form properties for checked fields should be strings: the browser sends text, and a float or boolean compares as something else.

## Elsewhere: the facade
```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::passes($formData, $submitToken, $validationToken, ['email', 'message']); // bool, fails closed
Mosparo::verify($formData, $submitToken, $validationToken);                       // Verification data object
Mosparo::project('newsletter')->passes(/* ... */);                                 // another configured project
```

Reading data returns read-only objects from `ExeQue\Mosparo\Data`: `Mosparo::statistics(days: 30)`, `Mosparo::rulePackageRules($id)`, `Mosparo::rulePackageRuleItems($id, $ruleId)`. These can be cached (`MOSPARO_CACHE=true`, or `Mosparo::withCache()` / `withoutCache()` per call); checks of a submission never are. Clear with `Mosparo::flushCache()` or `php artisan mosparo:clear-cache`, which touch only mosparo's entries.

## Testing
Never call mosparo from a test. Fake it:

```php
use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Mosparo as MosparoManager;

$fake = Mosparo::fake();               // accepts every check
Mosparo::fake()->reject();             // rejects every check
Mosparo::fake()->reject(1);            // rejects the first check, accepts the rest
Mosparo::fake()->reject()->sequence(true, false); // rejected, accepted, then rejected
Mosparo::fake()->reject(fn (array $formData, string $project) => str_contains($formData['message'], 'pills'));
```

A check still needs both tokens. Give them to a Livewire component the way the box does, and to a plain post as fields:

```php
Livewire::test(SignIn::class)
    ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
    ->set('form.email', 'ada@example.com')
    ->call('signIn');

$this->post('/newsletter', ['email' => 'ada@example.com', '_mosparo_submitToken' => 's', '_mosparo_validationToken' => 'v']);
```

Assert with `$fake->assertChecked()`, `assertChecked(fn (array $check) => $check['fields'] === ['email'])`, `assertNotChecked()` and `assertMetadataStored([...])`. `MOSPARO_ENABLED=false` lets every check pass without tokens, for tests that do not care about the spam check.

## Configuration
`MOSPARO_HOST` (the installation's origin, without a path), `MOSPARO_UUID`, `MOSPARO_PUBLIC_KEY`, `MOSPARO_PRIVATE_KEY`, and `MOSPARO_ENABLED`. With mosparo enabled, a missing value throws `IncompleteProject` rather than letting forms through. Several projects go under `projects` in `config/mosparo.php` and are named at the box (`@mosparo('newsletter')`) and at the check.

## Exceptions
All implement `ExeQue\Mosparo\Exceptions\MosparoException`: `ConnectionFailed` (mosparo cannot be reached), `RequestFailed` (mosparo refused or answered something unexpected), `UnknownProject`, `IncompleteProject`. `passes()`, `ValidMosparo` and `verify()` in Livewire report them and fail the check instead of throwing.
