<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('@mosparoScript', function () {
    it('loads the stylesheet and frontend of the default project', function () {
        expect(Blade::render('@mosparoScript'))
            ->toContain('<link href="https://mosparo.example/resources/project-uuid.css" rel="stylesheet">')
            ->toContain('<script src="https://mosparo.example/build/mosparo-frontend.js" defer></script>');
    });

    it('loads the stylesheet of a named project', function () {
        config()->set('mosparo.projects.newsletter', [
            'host' => 'https://mosparo.example',
            'uuid' => 'newsletter-uuid',
            'public_key' => 'newsletter-public',
            'private_key' => 'newsletter-private',
        ]);

        expect(Blade::render("@mosparoScript('newsletter')"))
            ->toContain('/resources/newsletter-uuid.css');
    });

    it('renders nothing when switched off', function () {
        config()->set('mosparo.enabled', false);

        expect(trim(Blade::render('@mosparoScript')))->toBe('');
    });

    it('escapes the configured values', function () {
        config()->set('mosparo.projects.default.uuid', '"><script>alert(1)</script>');

        expect(Blade::render('@mosparoScript'))->not->toContain('<script>alert(1)</script>');
    });
});

describe('the box', function () {
    it('renders the box with the project it belongs to', function (string $template) {
        expect(Blade::render($template))
            ->toContain('<div wire:ignore id="mosparo-box" data-mosparo-box')
            ->toContain("new mosparo(box.id, 'https:\\/\\/mosparo.example', 'project-uuid', 'public-key'")
            ->not->toContain('private-key');
    })->with([
        'directive' => '@mosparo',
        'component' => '<x-mosparo />',
    ]);

    it('renders the box of a named project', function (string $template) {
        config()->set('mosparo.projects.newsletter', [
            'host' => 'https://mosparo.example',
            'uuid' => 'newsletter-uuid',
            'public_key' => 'newsletter-public',
            'private_key' => 'newsletter-private',
        ]);

        expect(Blade::render($template))
            ->toContain("'newsletter-uuid', 'newsletter-public'");
    })->with([
        'directive' => "@mosparo('newsletter')",
        'component' => '<x-mosparo project="newsletter" />',
    ]);

    it('takes an id, mosparo options and attributes', function () {
        expect(Blade::render('<x-mosparo id="signup-box" class="mt-4" :options="[\'language\' => \'da\']" />'))
            ->toContain('id="signup-box"')
            ->toContain('class="mt-4"')
            ->toContain("JSON.parse('{\\u0022language\\u0022:\\u0022da\\u0022}')");
    });

    it('renders nothing when switched off', function (string $template) {
        config()->set('mosparo.enabled', false);

        expect(trim(Blade::render($template)))->toBe('');
    })->with([
        'directive' => '@mosparo',
        'component' => '<x-mosparo />',
    ]);
});

describe('compiling the directives', function () {
    it('compiles to a call on the named or default project', function (string $directive, string $compiled) {
        expect(Blade::compileString($directive))->toBe($compiled);
    })->with([
        'script' => ['@mosparoScript', '<?php echo app(\ExeQue\Mosparo\Mosparo::class)->project(null)->script(); ?>'],
        'named script' => ["@mosparoScript('newsletter')", "<?php echo app(\\ExeQue\\Mosparo\\Mosparo::class)->project('newsletter')->script(); ?>"],
        'box' => ['@mosparo', '<?php echo \Illuminate\Support\Facades\Blade::render(\'<x-mosparo :project="$project" />\', [\'project\' => null]); ?>'],
        'named box' => ["@mosparo('newsletter')", '<?php echo \Illuminate\Support\Facades\Blade::render(\'<x-mosparo :project="$project" />\', [\'project\' => \'newsletter\']); ?>'],
    ]);
});
