<?php

declare(strict_types=1);

use ExeQue\Mosparo\Mosparo;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Fixtures\Livewire\Contact;

function contact(): Testable
{
    return Livewire::test(Contact::class)
        ->set('form.email', 'ada@example.com')
        ->set('form.message', 'Hello')
        ->call('updateMosparo', ['submitToken' => 'submit', 'validationToken' => 'validation']);
}

describe('receiving the tokens', function () {
    it('takes the tokens from the box', function () {
        contact()
            ->assertSet('mosparo.submitToken', 'submit')
            ->assertSet('mosparo.validationToken', 'validation');
    });

    it('drops tokens that are not strings', function () {
        Livewire::test(Contact::class)
            ->call('updateMosparo', ['submitToken' => ['submit'], 'validationToken' => 42])
            ->assertSet('mosparo.submitToken', '')
            ->assertSet('mosparo.validationToken', '');
    });

    it('renders the box with the component', function () {
        Livewire::test(Contact::class)->assertSee('data-mosparo-box', escape: false);
    });
});

describe('verifying', function () {
    it('lets the submission through when mosparo accepts it', function () {
        $requests = mosparoAnswers();

        contact()
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        expect($requests[0]['formData'])->toBe([
            'email' => hash('sha256', 'ada@example.com'),
            'message' => hash('sha256', 'Hello'),
        ]);
    });

    it('takes the fields as an array too', function () {
        mosparoAnswers();

        contact()->call('sendArray')->assertSet('sent', true);
    });

    it('stops the submission when mosparo rejects it', function () {
        mosparoAnswers(valid: false);

        contact()
            ->call('send')
            ->assertHasErrors(['mosparo.submitToken'])
            ->assertSee('The spam check failed. Please try again.')
            ->assertSet('sent', false);
    });

    it('asks for new tokens once mosparo has seen them', function (bool $valid) {
        mosparoAnswers(valid: $valid);

        $component = contact()->call('send');

        $component
            ->assertDispatched(Mosparo::RESET_EVENT, id: $component->id())
            ->assertSet('mosparo.submitToken', '')
            ->assertSet('mosparo.validationToken', '');
    })->with([
        'accepted' => [true],
        'rejected' => [false],
    ]);

    it('keeps the tokens when the form fails before mosparo is asked', function () {
        $requests = mosparoAnswers();

        contact()
            ->set('form.email', 'not an email')
            ->call('send')
            ->assertHasErrors(['form.email'])
            ->assertNotDispatched(Mosparo::RESET_EVENT)
            ->assertSet('mosparo.submitToken', 'submit');

        expect($requests)->toHaveCount(0);
    });

    it('needs the tokens', function () {
        $requests = mosparoAnswers();

        Livewire::test(Contact::class)
            ->set('form.email', 'ada@example.com')
            ->set('form.message', 'Hello')
            ->call('send')
            ->assertHasErrors(['mosparo.submitToken' => 'required', 'mosparo.validationToken' => 'required'])
            ->assertSet('sent', false);

        expect($requests)->toHaveCount(0);
    });

    it('lets everything through when switched off', function () {
        config()->set('mosparo.enabled', false);

        Livewire::test(Contact::class)
            ->set('form.email', 'ada@example.com')
            ->set('form.message', 'Hello')
            ->call('send')
            ->assertHasNoErrors()
            ->assertNotDispatched(Mosparo::RESET_EVENT)
            ->assertSet('sent', true);
    });
});
