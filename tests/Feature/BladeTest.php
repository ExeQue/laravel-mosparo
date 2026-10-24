<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ViewException;

function newsletterBox(): void
{
    config()->set('mosparo.projects.newsletter', [
        'host' => 'https://newsletter.example',
        'uuid' => 'newsletter-uuid',
        'public_key' => 'newsletter-public',
        'private_key' => 'newsletter-private',
    ]);
}

/**
 * The configuration a rendered box hands to mosparo's frontend.
 *
 * @return array<string, mixed>
 */
function boxConfig(string $html): array
{
    preg_match('/data-mosparo="([^"]+)"/', $html, $matches);

    return json_decode(html_entity_decode($matches[1]), true);
}

describe('@mosparoScript', function () {
    it('loads mosparo\'s frontend and starts the boxes once it is there', function () {
        expect(Blade::render('@mosparoScript'))
            ->toContain('<script src="https://mosparo.example/build/mosparo-frontend.js" defer onload="window.laravelMosparo.start()"></script>')
            ->toContain('window.laravelMosparo = { start };');
    });

    it('loads the frontend from a named project\'s host', function () {
        newsletterBox();

        expect(Blade::render("@mosparoScript('newsletter')"))
            ->toContain('src="https://newsletter.example/build/mosparo-frontend.js"');
    });

    it('renders nothing when switched off', function () {
        config()->set('mosparo.enabled', false);

        expect(trim(Blade::render('@mosparoScript')))->toBe('');
    });

    it('escapes the host', function () {
        config()->set('mosparo.projects.default.host', 'https://x.example/"><script>alert(1)</script>');

        expect(Blade::render('@mosparoScript'))->not->toContain('<script>alert(1)</script>');
    });
});

describe('the box', function () {
    it('renders the box with the project it belongs to', function (string $template) {
        $html = Blade::render($template);

        expect($html)->toContain('wire:ignore')->not->toContain('private-key')
            ->and(boxConfig($html))->toBe([
                'host' => 'https://mosparo.example',
                'uuid' => 'project-uuid',
                'publicKey' => 'public-key',
                'options' => [],
            ]);
    })->with([
        'directive' => '@mosparo',
        'component' => '<x-mosparo />',
    ]);

    it('renders the box of a named project', function (string $template) {
        newsletterBox();

        expect(boxConfig(Blade::render($template)))
            ->uuid->toBe('newsletter-uuid')
            ->publicKey->toBe('newsletter-public');
    })->with([
        'directive' => "@mosparo('newsletter')",
        'component' => '<x-mosparo project="newsletter" />',
    ]);

    it('gives every box an id of its own', function () {
        preg_match_all('/id="(mosparo-[A-Za-z0-9]+)"/', Blade::render('@mosparo @mosparo'), $matches);

        expect($matches[1])->toHaveCount(2)
            ->and($matches[1][0])->not->toBe($matches[1][1]);
    });

    it('takes an id, mosparo options and attributes', function () {
        $html = Blade::render('<x-mosparo id="signup-box" class="mt-4" :options="[\'language\' => \'da\']" />');

        expect($html)->toContain('id="signup-box"')->toContain('class="mt-4"')
            ->and(boxConfig($html)['options'])->toBe(['language' => 'da']);
    });

    it('renders nothing when switched off', function (string $template) {
        config()->set('mosparo.enabled', false);

        expect(trim(Blade::render($template)))->toBe('');
    })->with([
        'directive' => '@mosparo',
        'component' => '<x-mosparo />',
    ]);

    it('throws while a project misses a value', function () {
        config()->set('mosparo.projects.default.uuid', null);

        Blade::render('<x-mosparo />');
    })->throws(ViewException::class, 'The mosparo project [default] is missing its uuid.');
});

describe('@mosparoError', function () {
    it('shows the error of a Livewire form or a plain form', function (string $key) {
        $errors = new ViewErrorBag()->put('default', new MessageBag([$key => 'Stopped.']));

        expect(trim(Blade::render('@mosparoError<p>{{ $message }}</p>@enderror', ['errors' => $errors])))
            ->toBe('<p>Stopped.</p>');
    })->with([
        'mosparo.submitToken',
        'mosparo.validationToken',
        '_mosparo_submitToken',
        '_mosparo_validationToken',
    ]);

    it('shows nothing without an error', function () {
        expect(trim(Blade::render('@mosparoError<p>{{ $message }}</p>@enderror', ['errors' => new ViewErrorBag()])))->toBe('')
            ->and(trim(Blade::render('@mosparoError<p>{{ $message }}</p>@enderror')))->toBe('');
    });

    it('opens the way @error does, so @enderror closes it', function () {
        expect(Blade::compileString('@mosparoError'))
            ->toContain('\\ExeQue\\Mosparo\\Support\\ErrorMessage::from($errors ?? null)')
            ->toContain('$__messageOriginal = $message;');
    });

    it('gives $message back afterwards', function () {
        $errors = new ViewErrorBag()->put('default', new MessageBag(['mosparo.submitToken' => 'Stopped.']));

        expect(trim(Blade::render('@mosparoError{{ $message }}@enderror {{ $message }}', ['errors' => $errors, 'message' => 'Mine'])))
            ->toBe('Stopped. Mine');
    });
});

describe('compiled views', function () {
    it('reads the configuration when rendering, not when compiling', function (string $template) {
        $first = Blade::render($template);

        newsletterBox();
        config()->set('mosparo.default', 'newsletter');

        expect(Blade::render($template))->not->toBe($first)->toContain('newsletter');

        config()->set('mosparo.enabled', false);

        expect(trim(Blade::render($template)))->toBe('');
    })->with([
        'script' => '@mosparoScript',
        'directive' => '@mosparo',
        'component' => '<x-mosparo />',
    ]);

    it('compiles to a call on the named or default project', function (string $directive, string $compiled) {
        expect(Blade::compileString($directive))->toBe($compiled);
    })->with([
        'script' => ['@mosparoScript', '<?php echo app(\ExeQue\Mosparo\Mosparo::class)->project(null)->script(); ?>'],
        'named script' => ["@mosparoScript('newsletter')", "<?php echo app(\\ExeQue\\Mosparo\\Mosparo::class)->project('newsletter')->script(); ?>"],
        'box' => ['@mosparo', '<?php echo \Illuminate\Support\Facades\Blade::render(\'<x-mosparo :project="$project" />\', [\'project\' => null]); ?>'],
        'named box' => ["@mosparo('newsletter')", '<?php echo \Illuminate\Support\Facades\Blade::render(\'<x-mosparo :project="$project" />\', [\'project\' => \'newsletter\']); ?>'],
    ]);
});
