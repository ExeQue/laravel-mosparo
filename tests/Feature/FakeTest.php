<?php

declare(strict_types=1);

use ExeQue\Mosparo\Facades\Mosparo;
use ExeQue\Mosparo\Mosparo as MosparoManager;
use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\Fixtures\Livewire\Contact;

it('answers checks with the result it was given, without asking mosparo', function (bool $passes) {
    $requests = mosparoAnswers(valid: ! $passes);
    $fake = Mosparo::fake($passes);

    expect(Mosparo::passes(['email' => 'ada@example.com'], 'submit', 'validation', ['email']))->toBe($passes)
        ->and($requests)->toHaveCount(0)
        ->and($fake->checks)->toBe([['project' => 'default', 'formData' => ['email' => 'ada@example.com'], 'fields' => ['email']]]);
})->with([true, false]);

it('still needs both tokens', function () {
    Mosparo::fake();

    expect(Mosparo::passes([], null, 'validation', []))->toBeFalse();
});

it('works through the rule, the Livewire form and the box', function () {
    Mosparo::fake(passes: false);
    config()->set('mosparo.projects', []);

    expect(Validator::make(['_mosparo_submitToken' => 'a', '_mosparo_validationToken' => 'b'], [
        '_mosparo_submitToken' => [new ValidMosparo([], project: 'anything')],
    ])->fails())->toBeTrue()
        ->and(Blade::render('@mosparoScript @mosparo'))->toContain('data-mosparo=');

    Livewire::test(Contact::class)
        ->set('form.email', 'ada@example.com')
        ->set('form.message', 'Hello')
        ->dispatch(MosparoManager::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation')
        ->call('send')
        ->assertHasErrors('mosparo.submitToken');
});
