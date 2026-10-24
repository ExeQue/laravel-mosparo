<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use ExeQue\Mosparo\Support\ErrorMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MosparoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mosparo.php', 'mosparo');

        $this->app->scoped(Mosparo::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mosparo');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mosparo');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');

        Blade::directive('mosparoScript', $this->compileScript(...));
        Blade::directive('mosparo', $this->compileBox(...));
        Blade::directive('mosparoError', $this->compileError(...));

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/mosparo.php' => config_path('mosparo.php')], 'mosparo-config');
            $this->publishes([__DIR__.'/../lang' => $this->app->langPath('vendor/mosparo')], 'mosparo-lang');
        }
    }

    /**
     * `@mosparoScript` or `@mosparoScript('newsletter')`: mosparo's frontend
     * from the project's host, for the page head.
     */
    private function compileScript(string $project): string
    {
        return '<?php echo app(\\'.Mosparo::class.'::class)->project('.$this->projectExpression($project).')->script(); ?>';
    }

    /**
     * `@mosparo` or `@mosparo('newsletter')`: the box, as `<x-mosparo />` renders it.
     */
    private function compileBox(string $project): string
    {
        return "<?php echo view('mosparo::components.mosparo', ['project' => ".$this->projectExpression($project).", 'attributes' => new \\Illuminate\\View\\ComponentAttributeBag])->render(); ?>";
    }

    /**
     * `@mosparoError ... @enderror`: like `@error`, with the spam check's error
     * in `$message`, from a Livewire component or a plain form. It opens the
     * same way `@error` does, so Laravel's own `@enderror` closes it.
     */
    private function compileError(): string
    {
        return '<?php if (($__mosparoMessage = \\'.ErrorMessage::class.'::from($errors ?? null)) !== null) : '
            .'if (isset($message)) { $__messageOriginal = $message; } '
            .'$message = $__mosparoMessage; ?>';
    }

    private function projectExpression(string $project): string
    {
        return $project === '' ? 'null' : $project;
    }
}
