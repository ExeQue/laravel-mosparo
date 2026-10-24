<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Workbench\App\Livewire\Comment;

/**
 * Points the demo at a local mosparo: the default project shows the invisible
 * box, the "visible" project the checkbox. Under lerd these are projects of the
 * mosparo service, keyed by the site handle (see CONTRIBUTING.md);
 * docker-compose.yml sets the MOSPARO_* variables for its own container.
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
        config()->set('mosparo.projects.visible', [
            'uuid' => env('MOSPARO_VISIBLE_UUID', 'lerd-laravel_mosparo_visible'),
            'public_key' => env('MOSPARO_VISIBLE_PUBLIC_KEY', 'lerd-laravel_mosparo_visible-public'),
            'private_key' => env('MOSPARO_VISIBLE_PRIVATE_KEY', 'lerd-laravel_mosparo_visible-private'),
            'client_arguments' => ['timeout' => 10],
        ]);
    }

    public function boot(): void
    {
        Livewire::component('comment', Comment::class);
    }
}
