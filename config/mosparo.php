<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | When disabled, every check passes and the box and script render nothing:
    | for local work without mosparo, tests and screenshots. When enabled, a
    | project missing its host, uuid or a key throws an IncompleteProject
    | exception instead of letting forms through.
    |
    */

    'enabled' => (bool) env('MOSPARO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Host
    |--------------------------------------------------------------------------
    |
    | The mosparo installation, for every project that sets no host of its
    | own: its origin, such as https://mosparo.example.com, without a path.
    |
    */

    'host' => env('MOSPARO_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Client arguments
    |--------------------------------------------------------------------------
    |
    | Guzzle options for the verification request, for every project. A
    | project's own client_arguments are merged over these. Add
    | 'verify' => false for a local mosparo with a self-signed certificate.
    |
    */

    'client_arguments' => [
        'timeout' => (int) env('MOSPARO_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Caches what is read from mosparo: statistics and rule packages. Checks
    | of a submission are never cached. A project's own 'cache' settings are
    | merged over these. Clear with `php artisan mosparo:clear-cache` or
    | Mosparo::flushCache(), which touch nothing but mosparo's entries.
    |
    */

    'cache' => [
        'enabled' => (bool) env('MOSPARO_CACHE', false),
        'store' => env('MOSPARO_CACHE_STORE'), // the default cache store when null
        'ttl' => (int) env('MOSPARO_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default project
    |--------------------------------------------------------------------------
    |
    | The project used when a form names none.
    |
    */

    'default' => env('MOSPARO_PROJECT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    |
    | One entry per mosparo project, with the uuid and keys from the project's
    | settings in mosparo. Name a project at the box and at the check:
    | @mosparo('newsletter'), new ValidMosparo('newsletter') or
    | $this->mosparo->verify($this->form, 'newsletter').
    |
    */

    'projects' => [

        'default' => [
            'uuid' => env('MOSPARO_UUID'),
            'public_key' => env('MOSPARO_PUBLIC_KEY'),
            'private_key' => env('MOSPARO_PRIVATE_KEY'),
        ],

        // 'newsletter' => [
        //     'host' => env('MOSPARO_NEWSLETTER_HOST'), // falls back to 'host' above
        //     'uuid' => env('MOSPARO_NEWSLETTER_UUID'),
        //     'public_key' => env('MOSPARO_NEWSLETTER_PUBLIC_KEY'),
        //     'private_key' => env('MOSPARO_NEWSLETTER_PRIVATE_KEY'),
        //     'client_arguments' => [], // merged over 'client_arguments' above
        //     'cache' => ['ttl' => 60], // merged over 'cache' above
        // ],

    ],

];
