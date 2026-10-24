<?php

declare(strict_types=1);

use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Mosparo as MosparoManager;
use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\Fixtures\Livewire\Contact;

it('accepts or rejects checks as told, without asking mosparo', function (bool $passes) {
    $requests = mosparoAnswers(valid: ! $passes);
    $fake = $passes ? Mosparo::fake() : Mosparo::fake()->reject();

    expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', ['email']))->toBe($passes)
        ->and($requests)->toHaveCount(0)
        ->and($fake->checks)->toBe([['project' => 'default', 'formData' => ['email' => 'ada@example.com'], 'fields' => ['email'], 'passed' => $passes]]);
})->with([true, false]);

it('switches between accepting and rejecting', function () {
    $fake = Mosparo::fake()->reject();

    expect(Mosparo::passes([], 'submit', 'validation', []))->toBeFalse();

    $fake->accept();

    expect(Mosparo::passes([], 'submit', 'validation', []))->toBeTrue();
});

it('decides check by check with a callback', function (string $method, string $message, bool $accepted) {
    Mosparo::fake()->{$method}(fn (array $formData, string $project): bool => $project === 'default' && str_contains($formData['message'], 'pills'));

    expect(Mosparo::passes(['message' => $message], 'submit', 'validation', []))->toBe($accepted)
        ->and(Mosparo::verify(['message' => $message], 'submit', 'validation')->submittable)->toBe($accepted);
})->with([
    'rejecting what matches' => ['reject', 'Cheap pills', false],
    'accepting what does not match' => ['reject', 'Hello', true],
    'accepting what matches' => ['accept', 'Cheap pills', true],
    'rejecting what does not match' => ['accept', 'Hello', false],
]);

it('still needs both tokens', function () {
    Mosparo::fake();

    expect(Mosparo::passes([], null, 'validation', []))->toBeFalse();
});

it('works through the rule, the Livewire form and the box', function () {
    Mosparo::fake()->reject();
    config()->set('mosparo.projects', ['default' => [], 'newsletter' => []]);

    expect(Validator::make(['_mosparo_submitToken' => 'a', '_mosparo_validationToken' => 'b'], [
        '_mosparo_submitToken' => [new ValidMosparo([], project: 'newsletter')],
    ])->fails())->toBeTrue()
        ->and(Blade::render('@mosparoScript @mosparo'))->toContain('data-mosparo=');

    Livewire::test(Contact::class)
        ->set('form.email', 'ada@example.com')
        ->set('form.message', 'Hello')
        ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
        ->call('send')
        ->assertHasErrors('mosparo.submitToken');
});

describe('the data API', function () {
    it('answers with what it was given, and nothing when given nothing', function () {
        $requests = mosparoAnswers();
        $statistics = new ExeQue\Mosparo\Data\Statistics(5, 1, null, []);
        $rules = new ExeQue\Mosparo\Data\RulePackageRules(true, [], 1, 3);
        $items = new ExeQue\Mosparo\Data\RulePackageRuleItems(true, [], 2, 2);

        Mosparo::fake()
            ->withStatistics($statistics)
            ->withRulePackageRules(3, $rules)
            ->withRulePackageRuleItems(3, 7, $items);

        expect(Mosparo::statistics())->toBe($statistics)
            ->and(Mosparo::withoutCache()->rulePackageRules(3))->toBe($rules)
            ->and(Mosparo::withCache()->rulePackageRuleItems(3, 7))->toBe($items)
            ->and(Mosparo::rulePackageRules(4)->rules)->toBe([])
            ->and(Mosparo::rulePackageRuleItems(3, 8)->items)->toBe([])
            ->and(Mosparo::streamRulePackageHashIndex(3, new SplTempFileObject()))->toBeTrue()
            ->and($requests)->toHaveCount(0);

        Mosparo::flushCache();
        Mosparo::flushCache('default');

        Mosparo::fake();

        expect(Mosparo::statistics())->validSubmissions->toBe(0);
    });

    it('records what was changed', function () {
        $fake = Mosparo::fake();
        config()->set('mosparo.projects.newsletter', []);

        expect(Mosparo::importRulePackage(3, '{}', 'hash'))->successful->toBeTrue()
            ->and(Mosparo::project('newsletter')->updateRulePackage(4, []))->successful->toBeTrue()
            ->and($fake->rulePackageChanges)->toBe([
                ['project' => 'default', 'rulePackageId' => 3, 'change' => 'import'],
                ['project' => 'newsletter', 'rulePackageId' => 4, 'change' => 'update'],
            ]);
    });

    it('answers a verification with its result', function (bool $passes, ExeQue\Mosparo\Enums\FieldStatus $status) {
        $fake = $passes ? Mosparo::fake() : Mosparo::fake()->reject();

        expect(Mosparo::verify(['email' => 'ada@example.com', 'address' => ['city' => 'Aarhus']], 'submit', 'validation'))
            ->submittable->toBe($passes)
            ->fields->toBe(['email' => $status, 'address[city]' => $status]);

        $fake->assertChecked();
    })->with([
        [true, ExeQue\Mosparo\Enums\FieldStatus::Valid],
        [false, ExeQue\Mosparo\Enums\FieldStatus::Invalid],
    ]);
});

it('refuses a project that is not configured, as mosparo does', function () {
    Mosparo::fake();

    Mosparo::project('missing');
})->throws(ExeQue\Mosparo\Exceptions\UnknownProject::class);

describe('assertions', function () {
    it('asserts checks', function () {
        $fake = Mosparo::fake();

        $fake->assertNotChecked();

        Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', []);

        $fake->assertChecked()
            ->assertChecked(fn (array $check): bool => $check['formData']['email'] === 'ada@example.com');

        expect(fn () => $fake->assertNotChecked())->toThrow(PHPUnit\Framework\AssertionFailedError::class)
            ->and(fn () => $fake->assertChecked(fn (): bool => false))->toThrow(PHPUnit\Framework\AssertionFailedError::class, 'No checked submission matched the callback.');
    });

    it('asserts stored metadata', function () {
        $fake = Mosparo::fake();

        expect(fn () => $fake->assertMetadataStored())->toThrow(PHPUnit\Framework\AssertionFailedError::class, 'No metadata was stored with mosparo.');

        Mosparo::storeMetadata('submit', 'validation', ['user' => 42]);

        $fake->assertMetadataStored()->assertMetadataStored(['user' => 42]);

        expect(fn () => $fake->assertMetadataStored(['user' => 1]))->toThrow(PHPUnit\Framework\AssertionFailedError::class, 'This metadata was not stored with mosparo.');
    });

    it('stores metadata from a Livewire form that passes', function () {
        $fake = Mosparo::fake();

        Livewire::test(Contact::class)
            ->set('form.email', 'ada@example.com')
            ->set('form.message', 'Hello')
            ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
            ->call('sendWithMetadata')
            ->assertSet('sent', true);

        $fake->assertMetadataStored(['source' => 'contact']);
    });
});
