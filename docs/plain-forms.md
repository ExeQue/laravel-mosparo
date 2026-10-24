<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Plain forms

Put `ValidMosparo` on the `_mosparo_submitToken` field. It checks the whole submission with the tokens the box placed in the form:

```php
use ExeQue\Mosparo\Rules\ValidMosparo;

class SubscribeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            '_mosparo_submitToken' => [new ValidMosparo],
        ];
    }
}
```

The rule also runs when the field is missing, so a form posted without the box fails. Show its message where the box is:

```blade
@mosparo
@error('_mosparo_submitToken') <p class="error">{{ $message }}</p> @enderror
```

After a post the page reloads, and the box starts with fresh tokens on its own.
