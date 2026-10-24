# Several projects

Separate forms can use separate mosparo projects, each with its own rules, design and statistics. Publish the configuration:

```bash
php artisan vendor:publish --tag=mosparo-config
```

Then add the project under `projects`:

```php
'projects' => [
    'default' => [ /* ... */ ],

    'newsletter' => [
        'uuid' => env('MOSPARO_NEWSLETTER_UUID'),
        'public_key' => env('MOSPARO_NEWSLETTER_PUBLIC_KEY'),
        'private_key' => env('MOSPARO_NEWSLETTER_PRIVATE_KEY'),
    ],
],
```

```dotenv
MOSPARO_NEWSLETTER_UUID=...
MOSPARO_NEWSLETTER_PUBLIC_KEY=...
MOSPARO_NEWSLETTER_PRIVATE_KEY=...
```

Every project uses the shared `host` and `client_arguments`. A project on another mosparo installation sets its own `host`, and a project's own `client_arguments` are merged over the shared ones.

Name the project at the box and at the check, with the same name in both places:

```blade
@mosparo('newsletter')
{{-- or --}}
<x-mosparo project="newsletter" />
```

```php
'_mosparo_submitToken' => [new ValidMosparo('newsletter')],

$this->mosparo->verify($this->form, 'newsletter');

Mosparo::project('newsletter')->passes($formData, $submitToken, $validationToken);
```

A box of a project on another host also needs that host's frontend in the head: `@mosparoScript('newsletter')`.

An unknown name throws an `UnknownProject` exception. `MOSPARO_PROJECT` changes which project is the default.
