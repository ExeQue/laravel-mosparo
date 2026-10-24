# Plain forms

Put `ValidMosparo` on the `_mosparo_submitToken` field. It checks the submission with the two tokens the box placed in the form:

```php
use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            '_mosparo_submitToken' => [new ValidMosparo(fields: ['email'])],
        ];
    }
}
```

The rule also runs when the field is missing, so a form posted without the box fails. Show its message next to the box:

```blade
@mosparo
@mosparoError <p class="error">{{ $message }}</p> @enderror
```

After a post, the page reloads and the box starts again with new tokens.

## Fields mosparo must check

mosparo checks the fields the box sends it. Someone sending spam can keep a field out of that check, for example by removing its `name` attribute before the box runs, and then add it back to the post. List the fields that matter in `fields`, and the rule fails unless mosparo checked each of them and found it valid:

```php
new ValidMosparo(fields: ['email', 'message'])
```

Passwords, hidden fields, checkboxes, radio buttons and files are never part of mosparo's check, so they cannot be listed.

## Another project

```php
new ValidMosparo('newsletter', fields: ['email'])
```

See [Several projects](projects.md).
