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
            '_mosparo_submitToken' => [ValidMosparo::make(['email'])],
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
ValidMosparo::make(['email', 'message'])
```

List every field a person types into. Passwords, hidden fields, checkboxes, radio buttons and files are never part of mosparo's check, so they cannot be listed. An empty list checks the tokens alone.

Fields are named as in the form: `address[street]`, or `rows[0][name]` in a repeated group.

## JSON and GraphQL inputs

An API input holds its fields and the two tokens in one object. Put the rule on that object; it reads the tokens from it and sends mosparo the other fields:

```php
// input: {email: "ada@example.com", _mosparo_submitToken: "…", _mosparo_validationToken: "…"}
'input' => [ValidMosparo::make(['email'])],
```

An API that names the tokens otherwise says so, and they are kept out of the fields sent to mosparo:

```php
// input: {email: "…", mosparo_submit_token: "…", mosparo_validation_token: "…"}
'input' => [ValidMosparo::make(['email'])->tokens('mosparo_submit_token', 'mosparo_validation_token')],
```

A failed check is reported on the submit token, `input.mosparo_submit_token` here, so a message given for that key is used:

```php
$messages = ['input.mosparo_submit_token' => 'Spamtjekket fejlede. Prøv igen.'];
```

The fields keep their names, so the form in the browser names its inputs `email`, not `input[email]`. mosparo checks the fields the browser sent it, and fails a field the server does not send back with the same value. Send the input exactly as the form holds it, including fields that are empty; fields the server has and the browser did not are ignored.

## Another project

```php
ValidMosparo::make(['email'], project: 'newsletter')
```

See [Several projects](projects.md).
