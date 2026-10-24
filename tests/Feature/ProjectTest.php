<?php

declare(strict_types=1);

use ExeQue\Mosparo\Exceptions\IncompleteProject;
use ExeQue\Mosparo\Exceptions\MosparoException;
use ExeQue\Mosparo\Exceptions\UnknownProject;
use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Project;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Exceptions;
use Mosparo\ApiClient\Exception as ClientException;

function newsletterProject(array $overrides = []): void
{
    config()->set('mosparo.projects.newsletter', [
        'uuid' => 'newsletter-uuid',
        'public_key' => 'newsletter-public',
        'private_key' => 'newsletter-private',
        ...$overrides,
    ]);
}

describe('choosing a project', function () {
    it('uses the default project when none is named', function () {
        expect(Mosparo::project())->toBeInstanceOf(Project::class)
            ->name->toBe('default')
            ->uuid()->toBe('project-uuid');
    });

    it('finds a named project', function () {
        newsletterProject(['host' => 'https://other.example']);

        expect(Mosparo::project('newsletter'))
            ->host()->toBe('https://other.example')
            ->uuid()->toBe('newsletter-uuid')
            ->publicKey()->toBe('newsletter-public');
    });

    it('follows the configured default', function () {
        newsletterProject();
        config()->set('mosparo.default', 'newsletter');

        expect(Mosparo::uuid())->toBe('newsletter-uuid');
    });

    it('refuses a project that is not configured', function () {
        expect(fn () => Mosparo::project('missing'))
            ->toThrow(UnknownProject::class, 'The mosparo project [missing] is not configured.')
            ->and(UnknownProject::named('missing'))->toBeInstanceOf(MosparoException::class);
    });
});

describe('sharing settings between projects', function () {
    it('shares the host with projects that set none', function () {
        config()->set('mosparo.host', 'https://shared.example/');
        config()->set('mosparo.projects.default.host', null);
        newsletterProject();

        expect(Mosparo::project('newsletter')->host())->toBe('https://shared.example')
            ->and(Mosparo::host())->toBe('https://shared.example');
    });

    it('prefers the host a project sets', function () {
        config()->set('mosparo.host', 'https://shared.example');

        expect(Mosparo::host())->toBe('https://mosparo.example');
    });

    it('merges a project\'s client arguments over the shared ones', function () {
        config()->set('mosparo.client_arguments', ['timeout' => 10, 'verify' => false]);
        newsletterProject(['client_arguments' => ['timeout' => 3]]);
        $requests = [];
        config()->set('mosparo.projects.newsletter.client_arguments.handler', function ($request, array $options) use (&$requests) {
            $requests[] = $options;

            return Create::promiseFor(new Response(200, [], '{"valid":false}'));
        });

        Mosparo::project('newsletter')->passes([], 'submit', 'validation', []);

        expect($requests[0])->toMatchArray(['timeout' => 3, 'verify' => false]);
    });
});

describe('switching it off', function () {
    it('is on unless switched off', function () {
        expect(Mosparo::enabled())->toBeTrue();

        config()->set('mosparo.enabled', false);

        expect(Mosparo::enabled())->toBeFalse();
    });

    it('lets everything through when off, without asking mosparo', function () {
        config()->set('mosparo.enabled', false);
        config()->set('mosparo.projects.default', []);
        $requests = mosparoAnswers(valid: false);

        expect(Mosparo::passes(['email' => 'ada@example.com'], null, null, []))->toBeTrue()
            ->and($requests)->toHaveCount(0);
    });
});

describe('a host with a path', function () {
    it('throws, as mosparo\'s API lives at the root of the host', function () {
        config()->set('mosparo.projects.default.host', 'https://example.com/mosparo');

        expect(fn () => Mosparo::verify([], 'submit', 'validation'))
            ->toThrow(IncompleteProject::class, 'must be the installation\'s origin, without a path: [https://example.com/mosparo]');
    });
});

describe('a project missing a value', function () {
    it('throws instead of letting forms through', function (string $key, string $missing) {
        config()->set('mosparo.host', null);
        config()->set("mosparo.projects.default.{$key}", null);

        expect(fn () => Mosparo::verify([], 'submit', 'validation'))
            ->toThrow(IncompleteProject::class, "The mosparo project [default] is missing its {$missing}.");
    })->with([
        ['host', 'host'],
        ['uuid', 'uuid'],
        ['public_key', 'public key'],
        ['private_key', 'private key'],
    ]);

    it('fails the check and reports why', function () {
        Exceptions::fake();
        config()->set('mosparo.projects.default.private_key', '');

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeFalse();

        Exceptions::assertReported(IncompleteProject::class);
    });
});

describe('checking a submission', function () {
    it('passes what mosparo accepts', function () {
        $requests = mosparoAnswers();

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeTrue()
            ->and($requests)->toHaveCount(1)
            ->and($requests[0]['submitToken'])->toBe('submit')
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
    });

    it('fails what mosparo rejects', function () {
        mosparoAnswers(valid: false);

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeFalse();
    });

    it('fails an answer that was not signed with the project key', function () {
        mosparoAnswers(forged: true);

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeFalse();
    });

    it('fails without asking when a token is missing', function (mixed $submitToken, mixed $validationToken) {
        $requests = mosparoAnswers();

        expect(Mosparo::passes([], $submitToken, $validationToken, []))->toBeFalse()
            ->and($requests)->toHaveCount(0);
    })->with([
        'no submit token' => [null, 'validation'],
        'no validation token' => ['submit', null],
        'empty tokens' => ['', ''],
        'not strings' => [['submit'], 42],
    ]);

    it('fails and reports when mosparo cannot be reached', function () {
        Exceptions::fake();
        mosparoIsUnreachable();

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeFalse();

        Exceptions::assertReported(ClientException::class);
    });

    it('fails and reports when mosparo answers nonsense', function () {
        Exceptions::fake();
        config()->set('mosparo.projects.default.client_arguments.handler', fn () => Create::promiseFor(new Response(200, [], 'true')));

        expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeFalse();

        Exceptions::assertReported(TypeError::class);
    });

    it('asks the named project', function () {
        newsletterProject();
        $requests = mosparoAnswers(project: 'newsletter');

        expect(Mosparo::project('newsletter')->passes(['email' => 'ada@example.com'], 'submit', 'validation', []))->toBeTrue()
            ->and($requests)->toHaveCount(1);
    });

    it('hands back the full result', function () {
        mosparoAnswers();

        expect(Mosparo::verify(['email' => 'ada@example.com'], 'submit', 'validation'))
            ->isSubmittable()->toBeTrue()
            ->isValid()->toBeTrue();
    });
});

describe('fields that must be checked', function () {
    it('passes when mosparo checked every field asked for', function () {
        mosparoAnswers();

        expect(Mosparo::passes(['email' => 'ada@example.com', 'message' => 'Hi'], 'submit', 'validation', ['message']))->toBeTrue();
    });

    it('fails when mosparo never saw a field asked for', function () {
        mosparoAnswers(verified: ['email']);

        expect(Mosparo::passes(['email' => 'ada@example.com', 'message' => 'Buy now'], 'submit', 'validation', ['message']))->toBeFalse();
    });
});
