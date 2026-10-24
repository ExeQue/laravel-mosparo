<?php

declare(strict_types=1);

use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Project;
use Illuminate\Support\Facades\Exceptions;
use Mosparo\ApiClient\Exception as MosparoException;

describe('choosing a project', function () {
    it('uses the default project when none is named', function () {
        expect(Mosparo::project())->toBeInstanceOf(Project::class)
            ->name->toBe('default')
            ->uuid()->toBe('project-uuid');
    });

    it('finds a named project', function () {
        config()->set('mosparo.projects.newsletter', [
            'host' => 'https://other.example',
            'uuid' => 'newsletter-uuid',
            'public_key' => 'newsletter-public',
            'private_key' => 'newsletter-private',
        ]);

        expect(Mosparo::project('newsletter'))
            ->host()->toBe('https://other.example')
            ->uuid()->toBe('newsletter-uuid')
            ->publicKey()->toBe('newsletter-public');
    });

    it('follows the configured default', function () {
        config()->set('mosparo.projects.newsletter', ['uuid' => 'newsletter-uuid']);
        config()->set('mosparo.default', 'newsletter');

        expect(Mosparo::uuid())->toBe('newsletter-uuid');
    });

    it('refuses a project that is not configured', function () {
        Mosparo::project('missing');
    })->throws(InvalidArgumentException::class, 'The mosparo project [missing] is not configured.');

    it('shares the host between projects that name none', function () {
        config()->set('mosparo.host', 'https://shared.example');
        config()->set('mosparo.projects.newsletter', ['uuid' => 'newsletter-uuid']);

        expect(Mosparo::project('newsletter')->host())->toBe('https://shared.example');
    });

    it('prefers the host a project names', function () {
        config()->set('mosparo.host', 'https://shared.example');

        expect(Mosparo::host())->toBe('https://mosparo.example');
    });

    it('drops a trailing slash from the host', function () {
        config()->set('mosparo.projects.default.host', 'https://mosparo.example/');

        expect(Mosparo::host())->toBe('https://mosparo.example');
    });
});

describe('switching it off', function () {
    it('is on when the project is complete', function () {
        expect(Mosparo::enabled())->toBeTrue();
    });

    it('is off when switched off', function () {
        config()->set('mosparo.enabled', false);

        expect(Mosparo::enabled())->toBeFalse();
    });

    it('is off while the project misses a value', function (string $key) {
        config()->set("mosparo.projects.default.{$key}", null);

        expect(Mosparo::enabled())->toBeFalse();
    })->with(['host', 'uuid', 'public_key', 'private_key']);

    it('lets everything through when off, without asking mosparo', function () {
        config()->set('mosparo.enabled', false);
        $requests = mosparoAnswers(valid: false);

        expect(Mosparo::passes(['email' => 'ada@example.com'], null, null))->toBeTrue()
            ->and($requests)->toHaveCount(0);
    });
});

describe('checking a submission', function () {
    it('passes what mosparo accepts', function () {
        $requests = mosparoAnswers();

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation'))->toBeTrue()
            ->and($requests)->toHaveCount(1)
            ->and($requests[0]['submitToken'])->toBe('submit')
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
    });

    it('fails what mosparo rejects', function () {
        mosparoAnswers(valid: false);

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation'))->toBeFalse();
    });

    it('fails an answer that was not signed with the project key', function () {
        mosparoAnswers(forged: true);

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation'))->toBeFalse();
    });

    it('fails without asking when a token is missing', function (?string $submitToken, ?string $validationToken) {
        $requests = mosparoAnswers();

        expect(Mosparo::passes([], $submitToken, $validationToken))->toBeFalse()
            ->and($requests)->toHaveCount(0);
    })->with([
        'no submit token' => [null, 'validation'],
        'no validation token' => ['submit', null],
        'empty tokens' => ['', ''],
    ]);

    it('fails and reports when mosparo cannot be reached', function () {
        Exceptions::fake();
        mosparoIsUnreachable();

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation'))->toBeFalse();

        Exceptions::assertReported(MosparoException::class);
    });

    it('asks the named project', function () {
        config()->set('mosparo.projects.newsletter', [
            'host' => 'https://mosparo.example',
            'uuid' => 'newsletter-uuid',
            'public_key' => 'newsletter-public',
            'private_key' => 'newsletter-private',
        ]);
        $requests = mosparoAnswers(project: 'newsletter');

        expect(Mosparo::project('newsletter')->passes(['email' => 'ada@example.com'], 'submit', 'validation'))->toBeTrue()
            ->and($requests)->toHaveCount(1);
    });

    it('hands back the full result', function () {
        mosparoAnswers();

        expect(Mosparo::verify(['email' => 'ada@example.com'], 'submit', 'validation'))
            ->isSubmittable()->toBeTrue()
            ->isValid()->toBeTrue();
    });
});
