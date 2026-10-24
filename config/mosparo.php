<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Switched off, every check passes and the box and script render nothing -
    | for screenshots and local work without a mosparo instance. A project
    | missing its host, uuid or keys is switched off on its own.
    |
    */

    'enabled' => (bool) env('MOSPARO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Host
    |--------------------------------------------------------------------------
    |
    | The mosparo installation, shared by every project that names no host
    | of its own.
    |
    */

    'host' => env('MOSPARO_HOST'),

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
    | One entry per mosparo project, from the project's settings in mosparo.
    | A project on another mosparo installation sets its own 'host'.
    | Forms with their own rules or statistics get a project each, named in
    | the form: @mosparo('newsletter'), new ValidMosparo('newsletter'),
    | $this->mosparo->verify($this->form, 'newsletter').
    |
    | client_arguments are Guzzle options for the verification request, such
    | as a timeout, or 'verify' => false against a self-signed local instance.
    |
    */

    'projects' => [

        'default' => [
            'uuid' => env('MOSPARO_UUID'),
            'public_key' => env('MOSPARO_PUBLIC_KEY'),
            'private_key' => env('MOSPARO_PRIVATE_KEY'),
            'client_arguments' => [
                'timeout' => (int) env('MOSPARO_TIMEOUT', 10),
            ],
        ],

        // 'newsletter' => [
        //     'host' => env('MOSPARO_NEWSLETTER_HOST'), // falls back to 'host' above
        //     'uuid' => env('MOSPARO_NEWSLETTER_UUID'),
        //     'public_key' => env('MOSPARO_NEWSLETTER_PUBLIC_KEY'),
        //     'private_key' => env('MOSPARO_NEWSLETTER_PRIVATE_KEY'),
        // ],

    ],

];
