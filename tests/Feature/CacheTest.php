<?php

declare(strict_types=1);

use ExeQue\Mosparo\Facades\Mosparo;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config()->set('mosparo.cache', ['enabled' => true, 'store' => 'array', 'ttl' => 300]);
});

function statisticsBody(int $valid): array
{
    return ['data' => [
        'numberOfValidSubmissions' => $valid,
        'numberOfSpamSubmissions' => 0,
        'lastSubmissionAt' => null,
        'numbersByDate' => [],
    ]];
}

function newsletterWithStatistics(array $cache = []): ArrayObject
{
    config()->set('mosparo.projects.newsletter', [
        'uuid' => 'newsletter-uuid',
        'public_key' => 'newsletter-public',
        'private_key' => 'newsletter-private',
        'cache' => $cache,
    ]);

    return mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2), statisticsBody(3)]], 'newsletter');
}

it('reads from mosparo once while the entry lasts', function () {
    $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);

    expect(Mosparo::statistics()->validSubmissions)->toBe(1)
        ->and(Mosparo::statistics()->validSubmissions)->toBe(1)
        ->and($requests)->toHaveCount(1);
});

it('keeps separate entries for separate questions', function () {
    $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);

    Mosparo::statistics(7);
    Mosparo::statistics(30);

    expect($requests)->toHaveCount(2);
});

it('reads every time while caching is off', function () {
    config()->set('mosparo.cache.enabled', false);
    $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);

    expect(Mosparo::statistics()->validSubmissions)->toBe(1)
        ->and(Mosparo::statistics()->validSubmissions)->toBe(2)
        ->and($requests)->toHaveCount(2);
});

it('is switched per project', function () {
    config()->set('mosparo.cache.enabled', false);
    $requests = newsletterWithStatistics(['enabled' => true]);

    Mosparo::project('newsletter')->statistics();
    Mosparo::project('newsletter')->statistics();

    expect($requests)->toHaveCount(1);
});

it('is switched per call', function () {
    $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);

    Mosparo::statistics();

    expect(Mosparo::withoutCache()->statistics()->validSubmissions)->toBe(2)
        ->and($requests)->toHaveCount(2);

    config()->set('mosparo.cache.enabled', false);
    Mosparo::flushCache();

    Mosparo::withCache(60)->statistics();
    Mosparo::withCache(60)->statistics();

    expect($requests)->toHaveCount(3);
});

it('expires entries after the TTL', function () {
    $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);
    config()->set('mosparo.cache.ttl', 60);

    Mosparo::statistics();
    $this->travel(61)->seconds();

    expect(Mosparo::statistics()->validSubmissions)->toBe(2)
        ->and($requests)->toHaveCount(2);
});

describe('clearing', function () {
    it('clears one project and leaves the others', function () {
        $default = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);
        $newsletter = newsletterWithStatistics();

        Mosparo::statistics();
        Mosparo::project('newsletter')->statistics();

        Mosparo::flushCache('newsletter');

        expect(Mosparo::statistics()->validSubmissions)->toBe(1)
            ->and(Mosparo::project('newsletter')->statistics()->validSubmissions)->toBe(2)
            ->and($default)->toHaveCount(1)
            ->and($newsletter)->toHaveCount(2);
    });

    it('clears every project', function () {
        $default = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);
        $newsletter = newsletterWithStatistics();

        Mosparo::statistics();
        Mosparo::project('newsletter')->statistics();

        Mosparo::flushCache();

        Mosparo::statistics();
        Mosparo::project('newsletter')->statistics();

        expect($default)->toHaveCount(2)->and($newsletter)->toHaveCount(2);
    });

    it('leaves the rest of the cache alone', function () {
        mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1)]]);
        Cache::store('array')->put('app:setting', 'kept', 300);

        Mosparo::statistics();
        Mosparo::flushCache();
        Mosparo::flushCache('default');

        expect(Cache::store('array')->get('app:setting'))->toBe('kept');
    });

    it('is cleared by changing a rule package', function () {
        $requests = mosparoApi([
            '/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)],
            '/api/v1/rule-package/3/batch' => ['result' => true, 'errors' => []],
        ]);

        Mosparo::statistics();
        Mosparo::updateRulePackage(3, []);

        expect(Mosparo::statistics()->validSubmissions)->toBe(2);
    });

    it('refuses to clear a project that is not configured', function () {
        $this->artisan('mosparo:clear-cache', ['project' => 'missing'])
            ->expectsOutputToContain('The mosparo project [missing] is not configured.')
            ->assertFailed();
    });

    it('is cleared from the console', function (array $arguments, string $output) {
        $requests = mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1), statisticsBody(2)]]);

        Mosparo::statistics();

        $this->artisan('mosparo:clear-cache', $arguments)->expectsOutputToContain($output)->assertSuccessful();

        expect(Mosparo::statistics()->validSubmissions)->toBe(2);
    })->with([
        'every project' => [[], 'Cleared the mosparo cache of every project.'],
        'one project' => [['project' => 'default'], 'Cleared the mosparo cache of the [default] project.'],
    ]);
});

it('keeps working when the cache may not restore objects', function () {
    config()->set('cache.stores.array.serialize', true);
    config()->set('cache.serializable_classes', false);
    Cache::forgetDriver('array');
    mosparoApi(['/api/v1/statistic/by-date' => [statisticsBody(1)]]);

    Mosparo::statistics();

    expect(Mosparo::statistics()->validSubmissions)->toBe(1);
});

it('does not keep a page mosparo did not deliver', function () {
    $requests = mosparoApi(['/api/v1/rule-package/3/rules' => [
        ['result' => false, 'page' => 1, 'totalPages' => 0, 'rules' => []],
        ['result' => true, 'page' => 1, 'totalPages' => 1, 'rules' => []],
    ]]);

    expect(Mosparo::rulePackageRules(3)->successful)->toBeFalse()
        ->and(Mosparo::rulePackageRules(3)->successful)->toBeTrue()
        ->and(Mosparo::rulePackageRules(3)->successful)->toBeTrue()
        ->and($requests)->toHaveCount(2);
});

it('never caches a check of a submission', function () {
    $requests = mosparoAnswers();

    Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []);
    Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []);

    expect($requests)->toHaveCount(2);
});
