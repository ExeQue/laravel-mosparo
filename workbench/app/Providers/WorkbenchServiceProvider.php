<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Points the demo at a local mosparo. Under lerd that is the mosparo service,
 * which creates a project per site keyed by the site handle; docker-compose.yml
 * sets the MOSPARO_* variables for its own mosparo container instead.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config()->set('mosparo.host', env('MOSPARO_HOST', 'https://mosparo.test'));
        config()->set('mosparo.projects.default', [
            'uuid' => env('MOSPARO_UUID', 'lerd-laravel_mosparo'),
            'public_key' => env('MOSPARO_PUBLIC_KEY', 'lerd-laravel_mosparo-public'),
            'private_key' => env('MOSPARO_PRIVATE_KEY', 'lerd-laravel_mosparo-private'),
            'client_arguments' => ['timeout' => 10],
        ]);
    }
}
