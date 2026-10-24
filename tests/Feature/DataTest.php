<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use ExeQue\Mosparo\Data\RulePackageRule;
use ExeQue\Mosparo\Data\RulePackageRuleItem;
use ExeQue\Mosparo\Data\StatisticsDay;
use ExeQue\Mosparo\Enums\FieldStatus;
use ExeQue\Mosparo\Exceptions\ConnectionFailed;
use ExeQue\Mosparo\Exceptions\MosparoException;
use ExeQue\Mosparo\Exceptions\RequestFailed;
use ExeQue\Mosparo\Facades\Mosparo;
use Illuminate\Support\Facades\Exceptions;

function statisticsResponse(): array
{
    return ['data' => [
        'numberOfValidSubmissions' => 12,
        'numberOfSpamSubmissions' => 3,
        'lastSubmissionAt' => '2026-10-24T09:21:08+00:00',
        'numbersByDate' => [
            '2026-10-24' => [
                'numberOfValidSubmissions' => 12,
                'numberOfSpamSubmissions' => 3,
                'numberOfDelayedRequests' => 1,
                'numberOfBlockedRequests' => 2,
            ],
        ],
    ]];
}

function rulesResponse(int $page = 1, int $totalPages = 2): array
{
    return ['result' => true, 'page' => $page, 'totalPages' => $totalPages, 'rules' => [[
        'id' => 7,
        'uuid' => 'rule-uuid',
        'type' => 'word',
        'name' => 'Pharmacy words',
        'description' => 'Words selling pills',
        'numberOfItems' => 40,
        'spamRatingFactor' => '1.5',
        'updatedAt' => '2026-10-20T10:00:00+00:00',
        'listRoute' => '/rules',
    ]]];
}

describe('statistics', function () {
    it('counts the submissions, in total and by day', function () {
        $requests = mosparoApi(['/api/v1/statistic/by-date' => statisticsResponse()]);

        $statistics = Mosparo::statistics(7, CarbonImmutable::parse('2026-10-18'));

        expect($statistics)
            ->validSubmissions->toBe(12)
            ->spamSubmissions->toBe(3)
            ->lastSubmissionAt->toEqual(CarbonImmutable::parse('2026-10-24 09:21:08'))
            ->and($statistics->days['2026-10-24'])->toEqual(new StatisticsDay(CarbonImmutable::parse('2026-10-24'), 12, 3, 1, 2))
            ->and($requests[0]->getUri()->getQuery())->toBe('range=604800&startDate=2026-10-18');
    });

    it('has no last submission for a project nobody used', function () {
        mosparoApi(['/api/v1/statistic/by-date' => ['data' => [
            'numberOfValidSubmissions' => 0,
            'numberOfSpamSubmissions' => 0,
            'lastSubmissionAt' => null,
            'numbersByDate' => [],
        ]]]);

        expect(Mosparo::statistics())->lastSubmissionAt->toBeNull()->days->toBe([]);
    });

    it('throws when mosparo refuses', function () {
        mosparoApi(['/api/v1/statistic/by-date' => ['error' => true, 'errorMessage' => 'Not allowed', 'debugInformation' => ['why' => 'key']]]);

        try {
            Mosparo::statistics();
        } catch (RequestFailed $exception) {
            expect($exception)->toBeInstanceOf(MosparoException::class)
                ->getMessage()->toBe('mosparo refused the request for the project [default]: Not allowed')
                ->debugInformation->toBe(['why' => 'key']);

            return;
        }

        $this->fail('Nothing was thrown.');
    });

    it('throws when mosparo answers something unexpected', function () {
        mosparoApi(['/api/v1/statistic/by-date' => ['data' => []]]);

        Mosparo::statistics();
    })->throws(RequestFailed::class, 'mosparo gave an unexpected answer for the project [default].');

    it('throws when mosparo turns the keys away', function () {
        config()->set('mosparo.projects.default.client_arguments.handler', fn ($request) => GuzzleHttp\Promise\Create::promiseFor(
            new GuzzleHttp\Psr7\Response(401, [], '{"error":true,"errorMessage":"Unauthorized"}'),
        ));
        config()->set('mosparo.projects.default.client_arguments.http_errors', true);

        Mosparo::statistics();
    })->throws(RequestFailed::class, 'mosparo refused the request for the project [default]');

    it('throws when mosparo cannot be reached', function () {
        mosparoIsUnreachable();

        Mosparo::statistics();
    })->throws(ConnectionFailed::class, 'Could not reach mosparo for the project [default].');
});

describe('rule packages', function () {
    it('reads a page of rules', function () {
        mosparoApi(['/api/v1/rule-package/3/rules' => rulesResponse()]);

        $rules = Mosparo::rulePackageRules(3);

        expect($rules)->successful->toBeTrue()->page->toBe(1)->totalPages->toBe(2)
            ->and($rules->hasMorePages())->toBeTrue()
            ->and($rules->rules[0])->toEqual(new RulePackageRule(
                7,
                'rule-uuid',
                'word',
                'Pharmacy words',
                'Words selling pills',
                40,
                1.5,
                CarbonImmutable::parse('2026-10-20 10:00:00'),
                '/rules',
            ));
    });

    it('reads a page of the items of a rule', function () {
        mosparoApi(['/api/v1/rule-package/3/rules/7/rule-items' => [
            'result' => true, 'page' => 2, 'totalPages' => 2,
            'ruleItems' => [['id' => 1, 'uuid' => 'item-uuid', 'type' => 'text', 'value' => 'pills', 'rating' => '2']],
        ]]);

        $items = Mosparo::rulePackageRuleItems(3, 7, page: 2);

        expect($items->items)->toEqual([new RulePackageRuleItem(1, 'item-uuid', 'text', 'pills', 2.0)])
            ->and($items->hasMorePages())->toBeFalse();
    });

    it('streams the hash index into a file', function () {
        mosparoApi(['/api/v1/rule-package/3/hash-index' => ['abc123', '###END']]);
        $target = new SplTempFileObject();
        $bytes = [];

        expect(Mosparo::streamRulePackageHashIndex(3, $target, function (int $total) use (&$bytes) {
            $bytes[] = $total;
        }))->toBeTrue();

        $target->rewind();

        expect($target->fread(100))->toBe('abc123')->and($bytes)->toBe([6]);
    });

    it('lets an exception from the progress callback through as it is', function () {
        mosparoApi(['/api/v1/rule-package/3/hash-index' => ['abc123', '###END']]);

        Mosparo::streamRulePackageHashIndex(3, new SplTempFileObject(), function () {
            throw new LogicException('The application\'s own failure');
        });
    })->throws(LogicException::class, 'The application\'s own failure');

    it('imports a package', function () {
        mosparoApi(['/api/v1/rule-package/import' => ['successful' => true, 'verifiedHash' => true]]);

        expect(Mosparo::importRulePackage(3, '{"rules":[]}', 'hash'))->successful->toBeTrue()->verifiedHash->toBeTrue();
    });

    it('updates a package in a batch', function () {
        mosparoApi(['/api/v1/rule-package/3/batch' => ['result' => false, 'errors' => ['Unknown rule']]]);

        expect(Mosparo::updateRulePackage(3, [['task' => 'delete']]))->successful->toBeFalse()->errors->toBe(['Unknown rule']);
    });
});

describe('metadata', function () {
    it('stores metadata with a verified submission', function () {
        $requests = mosparoApi(['/api/v1/verification/store-metadata' => ['result' => true]]);

        expect(Mosparo::storeMetadata('submit', 'validation', ['user' => 42]))->toBeTrue()
            ->and(json_decode((string) $requests[0]->getBody(), true)['metadata'])->toBe('{"user":42}');
    });
});

describe('verification', function () {
    it('hands back mosparo\'s full answer', function () {
        mosparoAnswers(verified: ['email']);

        expect(Mosparo::verify(['email' => 'ada@example.com', 'message' => 'Hi'], 'submit', 'validation'))
            ->submittable->toBeTrue()
            ->valid->toBeTrue()
            ->fields->toBe(['email' => FieldStatus::Valid])
            ->field('message')->toBe(FieldStatus::NotVerified);
    });
});

describe('metadata from Livewire', function () {
    it('stores metadata once the check passes, and carries on when it cannot', function () {
        Exceptions::fake();
        mosparoAnswers();

        Livewire\Livewire::test(Tests\Fixtures\Livewire\Contact::class)
            ->set('form.email', 'ada@example.com')
            ->set('form.message', 'Hello')
            ->dispatch(ExeQue\Mosparo\Mosparo::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
            ->call('sendWithMetadata')
            ->assertSet('sent', true);

        Exceptions::assertReported(RequestFailed::class);
    });
});
