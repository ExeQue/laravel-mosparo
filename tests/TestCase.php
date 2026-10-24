<?php

declare(strict_types=1);

namespace Tests;

use ExeQue\Mosparo\MosparoServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MosparoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('mosparo.enabled', true);
        $app['config']->set('mosparo.projects.default.host', 'https://mosparo.example');
        $app['config']->set('mosparo.projects.default.uuid', 'project-uuid');
        $app['config']->set('mosparo.projects.default.public_key', 'public-key');
        $app['config']->set('mosparo.projects.default.private_key', 'private-key');
    }
}
