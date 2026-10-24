<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Several projects

Separate forms can use separate mosparo projects, each with its own rules and statistics. Publish the configuration:

```bash
php artisan vendor:publish --tag=mosparo-config
```

Then add a project under `projects`:

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

Projects share `MOSPARO_HOST`. A project on another mosparo installation sets its own `'host'`.

Name the project in the head, at the box and at the check:

```blade
@mosparoScript('newsletter')

@mosparo('newsletter')
```

```php
'_mosparo_submitToken' => [new ValidMosparo('newsletter')],

$this->mosparo->verify($this->form, 'newsletter');
```

The project is always picked on the server, never from what the browser sends. `MOSPARO_PROJECT` changes which project is the default.
