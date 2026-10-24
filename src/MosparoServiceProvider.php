<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MosparoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mosparo.php', 'mosparo');

        $this->app->singleton(Mosparo::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mosparo');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mosparo');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');

        Blade::directive('mosparoScript', $this->compileScript(...));
        Blade::directive('mosparo', $this->compileBox(...));

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/mosparo.php' => config_path('mosparo.php')], 'mosparo-config');
            $this->publishes([__DIR__.'/../lang' => $this->app->langPath('vendor/mosparo')], 'mosparo-lang');
        }
    }

    /**
     * `@mosparoScript` or `@mosparoScript('newsletter')`: the project's
     * stylesheet and mosparo's frontend script, for the page head.
     */
    private function compileScript(string $project): string
    {
        $project = $project === '' ? 'null' : $project;

        return "<?php echo app(\\".Mosparo::class."::class)->project({$project})->script(); ?>";
    }

    /**
     * `@mosparo` or `@mosparo('newsletter')`: the box, as `<x-mosparo />` renders it.
     */
    private function compileBox(string $project): string
    {
        $project = $project === '' ? 'null' : $project;

        return "<?php echo \\Illuminate\\Support\\Facades\\Blade::render('<x-mosparo :project=\"\$project\" />', ['project' => {$project}]); ?>";
    }
}
