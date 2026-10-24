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
