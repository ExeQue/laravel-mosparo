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
        ->dispatch(Mosparo::CHECKED_EVENT, submitToken: 'submit', validationToken: 'validation');
}

describe('receiving the tokens', function () {
    it('takes the tokens the box dispatches', function () {
        contact()
            ->assertSet('mosparo.submitToken', 'submit')
            ->assertSet('mosparo.validationToken', 'validation');
    });

    it('drops tokens that are not strings', function () {
        Livewire::test(Contact::class)
            ->dispatch(Mosparo::CHECKED_EVENT, submitToken: ['submit'], validationToken: 42)
            ->assertSet('mosparo.submitToken', '')
            ->assertSet('mosparo.validationToken', '');
    });

    it('renders the box with the component', function () {
        Livewire::test(Contact::class)->assertSee('data-mosparo=', escape: false);
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

    it('stops the submission when mosparo never saw a field it must check', function () {
        mosparoAnswers(verified: ['email']);

        contact()->call('sendMessage')->assertSet('sent', false);
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

    it('shows the form\'s own errors first and keeps the tokens', function () {
        $requests = mosparoAnswers();

        Livewire::test(Contact::class)
            ->set('form.email', 'not an email')
            ->call('send')
            ->assertHasErrors(['form.email'])
            ->assertHasNoErrors(['mosparo.submitToken'])
            ->assertNotDispatched(Mosparo::RESET_EVENT);

        expect($requests)->toHaveCount(0);
    });

    it('needs the tokens, and says so in the package\'s words', function () {
        $requests = mosparoAnswers();

        Livewire::test(Contact::class)
            ->set('form.email', 'ada@example.com')
            ->set('form.message', 'Hello')
            ->call('send')
            ->assertHasErrors(['mosparo.submitToken' => 'required', 'mosparo.validationToken' => 'required'])
            ->assertSee('The spam check failed. Please try again.')
            ->assertSet('sent', false);

        expect($requests)->toHaveCount(0);
    });

    it('still validates the form when switched off', function () {
        config()->set('mosparo.enabled', false);

        Livewire::test(Contact::class)
            ->set('form.email', 'not an email')
            ->call('send')
            ->assertHasErrors(['form.email'])
            ->assertSet('sent', false);
    });

    it('lets a valid form through when switched off', function () {
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
