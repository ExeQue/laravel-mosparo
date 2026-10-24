# laravel-mosparo

**Documentation: [exeque.github.io/laravel-mosparo](https://exeque.github.io/laravel-mosparo/)**

[mosparo](https://mosparo.io) spam protection for Laravel and Livewire. Put the box in a form with one directive, and check the submission with a validation rule, a Livewire trait or a facade. If a form fails for another reason, such as a wrong password, it can simply be sent again.

## Install

```bash
composer require exeque/laravel-mosparo
```

```dotenv
MOSPARO_HOST=https://mosparo.example.com
MOSPARO_UUID=your-project-uuid
MOSPARO_PUBLIC_KEY=your-public-key
MOSPARO_PRIVATE_KEY=your-private-key
```

## Use

```blade
<head>
    @mosparoScript
</head>

<form method="post" action="/newsletter">
    @csrf
    <input name="email" type="email">

    @mosparo
    @mosparoError <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Subscribe</button>
</form>
```

A plain form post is checked by a validation rule:

```php
use ExeQue\Mosparo\Rules\ValidMosparo;

'_mosparo_submitToken' => [new ValidMosparo(fields: ['email'])],
```

A Livewire component uses the trait:

```php
use ExeQue\Mosparo\Livewire\Concerns\InteractsWithMosparo;

class SignIn extends Component
{
    use InteractsWithMosparo;

    public function signIn(): void
    {
        if (! $this->mosparo->verify($this->form, fields: ['email'])) {
            return;
        }

        // ...
    }
}
```

Anything else asks the facade:

```php
use ExeQue\Mosparo\Facades\Mosparo;

Mosparo::passes($formData, $submitToken, $validationToken, fields: ['message']);
```

## Features

- [Several mosparo projects](docs/projects.md) in one application, picked by name
- [Disabled](docs/switching-off.md) with one env key, for local work and tests
- Visible and invisible boxes, started wherever they appear: Livewire updates, `wire:navigate`, the back button
- The error message in [29 European languages](docs/translations.md)
- [A workbench](CONTRIBUTING.md) with a real mosparo, through lerd or Docker

Read on in the [documentation](docs/README.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
