<?php

declare(strict_types=1);

use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Support\Facades\Validator;

function validateWithMosparo(array $data, array $fields = [], ?string $project = null): Illuminate\Validation\Validator
{
    return Validator::make($data, [
        '_mosparo_submitToken' => [new ValidMosparo($fields, $project)],
    ]);
}

function withTokens(array $data): array
{
    return [...$data, '_mosparo_submitToken' => 'submit', '_mosparo_validationToken' => 'validation'];
}

it('passes a submission mosparo accepts', function () {
    $requests = mosparoAnswers();

    expect(validateWithMosparo(withTokens(['email' => 'ada@example.com']))->passes())->toBeTrue()
        ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
});

it('fails a submission mosparo rejects', function () {
    mosparoAnswers(valid: false);

    $validator = validateWithMosparo(withTokens(['email' => 'ada@example.com']));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('_mosparo_submitToken'))->toBe('The spam check failed. Please try again.');
});

it('fails a form posted without the box', function () {
    $requests = mosparoAnswers();

    expect(validateWithMosparo(['email' => 'ada@example.com'])->fails())->toBeTrue()
        ->and($requests)->toHaveCount(0);
});

it('fails when mosparo never saw a field it must check', function () {
    mosparoAnswers(verified: ['email']);

    expect(validateWithMosparo(withTokens(['email' => 'ada@example.com', 'message' => 'Buy now']), fields: ['message'])->fails())->toBeTrue();
});

it('sends nested fields under the names the form gave them', function () {
    $requests = mosparoAnswers();

    validateWithMosparo(withTokens(['address' => ['street' => 'Main St']]))->passes();

    expect($requests[0]['formData'])->toBe(['address[street]' => hash('sha256', 'Main St')]);
});

it('checks against the named project', function () {
    config()->set('mosparo.projects.newsletter', [
        'uuid' => 'newsletter-uuid',
        'public_key' => 'newsletter-public',
        'private_key' => 'newsletter-private',
    ]);
    $requests = mosparoAnswers(project: 'newsletter');

    expect(validateWithMosparo(withTokens([]), project: 'newsletter')->passes())->toBeTrue()
        ->and($requests)->toHaveCount(1);
});

it('lets everything through when switched off', function () {
    config()->set('mosparo.enabled', false);

    expect(validateWithMosparo([])->passes())->toBeTrue();
});

describe('on the object that holds the fields and the tokens, as JSON and GraphQL send them', function () {
    function validateObject(array $input, ValidMosparo $rule, array $messages = []): Illuminate\Validation\Validator
    {
        return Validator::make(['input' => $input], ['input' => [$rule]], $messages);
    }

    it('checks the fields of the object with its tokens', function () {
        $requests = mosparoAnswers();

        $validator = validateObject(withTokens(['email' => 'ada@example.com']), ValidMosparo::make(['email']));

        expect($validator->passes())->toBeTrue()
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')])
            ->and($requests[0]['submitToken'])->toBe('submit');
    });

    it('reads the tokens under the names it is given, and keeps them out of the fields', function () {
        $requests = mosparoAnswers();

        $validator = validateObject(
            ['email' => 'ada@example.com', 'mosparo_submit_token' => 'submit', 'mosparo_validation_token' => 'validation'],
            ValidMosparo::make(['email'])->tokens('mosparo_submit_token', 'mosparo_validation_token'),
        );

        expect($validator->passes())->toBeTrue()
            ->and($requests[0]['submitToken'])->toBe('submit')
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
    });

    it('reports a rejected check on the submit token', function () {
        mosparoAnswers(valid: false);

        $validator = validateObject(withTokens(['email' => 'ada@example.com']), ValidMosparo::make(['email']));

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->keys())->toBe(['input._mosparo_submitToken'])
            ->and($validator->errors()->first('input._mosparo_submitToken'))->toBe('The spam check failed. Please try again.');
    });

    it('uses a message given for the submit token', function () {
        mosparoAnswers(valid: false);

        $validator = validateObject(
            ['mosparo_submit_token' => 'submit', 'mosparo_validation_token' => 'validation'],
            ValidMosparo::make()->tokens('mosparo_submit_token', 'mosparo_validation_token'),
            ['input.mosparo_submit_token' => 'Spamtjekket fejlede.'],
        );

        expect($validator->errors()->first('input.mosparo_submit_token'))->toBe('Spamtjekket fejlede.');
    });

    it('fails an object without both tokens, without asking mosparo', function () {
        $requests = mosparoAnswers();

        $validator = validateObject(['email' => 'ada@example.com', '_mosparo_submitToken' => 'submit'], ValidMosparo::make());

        expect($validator->fails())->toBeTrue()
            ->and($requests)->toHaveCount(0);
    });

    it('checks a plain form post on its custom-named submit token', function () {
        $requests = mosparoAnswers();

        $validator = Validator::make(
            ['email' => 'ada@example.com', 'submit_token' => 'submit', 'validation_token' => 'validation'],
            ['submit_token' => [ValidMosparo::make(['email'])->tokens('submit_token', 'validation_token')]],
        );

        expect($validator->passes())->toBeTrue()
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
    });

    it('checks the fields next to a submit token nested in the data', function () {
        $requests = mosparoAnswers();

        $validator = Validator::make(
            ['contact' => withTokens(['email' => 'ada@example.com'])],
            ['contact._mosparo_submitToken' => [ValidMosparo::make(['email'])]],
        );

        expect($validator->passes())->toBeTrue()
            ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
    });
});
