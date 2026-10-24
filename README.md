# laravel-mosparo

**Documentation: [exeque.github.io/laravel-mosparo](https://exeque.github.io/laravel-mosparo/)**

[mosparo](https://mosparo.io) spam protection for Laravel and Livewire. Put the box in a form with one directive, and check the submission with a validation rule, a Livewire trait or a facade.

A mosparo submission can be verified once. Every check here spends the tokens and the box fetches new ones, so a form that fails for another reason - a wrong password, a taken email address - can simply be sent again.

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
    <button type="submit">Subscribe</button>
</form>
```

A plain form post checks with a validation rule:

```php
'_mosparo_submitToken' => [new ValidMosparo],
```

A Livewire component uses the trait:

```php
use InteractsWithMosparo;

public function signIn(): void
{
    if (! $this->mosparo->verify($this->form)) {
        return;
    }

    // ...
}
```

And anything else asks the facade:

```php
Mosparo::passes($formData, $submitToken, $validationToken);
```

## What else

- [Several mosparo projects](docs/projects.md) in one application, each picked by name on the server
- [Switched off](docs/switching-off.md) with one env key, for local work and tests
- The error message in [29 European languages](docs/translations.md)
- [A workbench](CONTRIBUTING.md) with real mosparo, through lerd or Docker

Read on in the [documentation](docs/README.md).

## License

MIT. See [LICENSE.md](LICENSE.md).
