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
            '_mosparo_submitToken' => [new ValidMosparo(['email'])],
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

mosparo checks the fields the box sends it. Someone sending spam can keep a field out of that check, for example by removing its `name` attribute before the box runs, and then add it back to the post. The rule therefore takes the fields that matter, and fails unless mosparo checked each of them and found it valid:

```php
new ValidMosparo(['email', 'message'])
```

List every field a person types into. Passwords, hidden fields, checkboxes, radio buttons and files are never part of mosparo's check, so they cannot be listed. An empty list checks the tokens alone.

Fields are named as in the form: `address[street]`, or `rows[0][name]` in a repeated group.

## Another project

```php
new ValidMosparo(['email'], project: 'newsletter')
```

See [Several projects](projects.md).
