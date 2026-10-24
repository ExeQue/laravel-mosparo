<?php

declare(strict_types=1);

use ExeQue\Mosparo\Rules\ValidMosparo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

function validateWithMosparo(array $data, ?string $project = null): Illuminate\Validation\Validator
{
    return Validator::make($data, [
        '_mosparo_submitToken' => [new ValidMosparo($project)],
    ]);
}

it('passes a submission mosparo accepts', function () {
    $requests = mosparoAnswers();

    $validator = validateWithMosparo([
        'email' => 'ada@example.com',
        '_mosparo_submitToken' => 'submit',
        '_mosparo_validationToken' => 'validation',
    ]);

    expect($validator->passes())->toBeTrue()
        ->and($requests[0]['formData'])->toBe(['email' => hash('sha256', 'ada@example.com')]);
});

it('fails a submission mosparo rejects', function () {
    mosparoAnswers(valid: false);

    $validator = validateWithMosparo([
        'email' => 'ada@example.com',
        '_mosparo_submitToken' => 'submit',
        '_mosparo_validationToken' => 'validation',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('_mosparo_submitToken'))->toBe('The spam check failed. Please try again.');
});

it('fails a form posted without the box', function () {
    $requests = mosparoAnswers();

    expect(validateWithMosparo(['email' => 'ada@example.com'])->fails())->toBeTrue()
        ->and($requests)->toHaveCount(0);
});

it('ignores tokens that are not strings', function () {
    $requests = mosparoAnswers();

    $validator = validateWithMosparo([
        '_mosparo_submitToken' => ['submit'],
        '_mosparo_validationToken' => 'validation',
    ]);

    expect($validator->fails())->toBeTrue()
        ->and($requests)->toHaveCount(0);
});

it('leaves uploaded files out of what mosparo sees', function () {
    $requests = mosparoAnswers();

    validateWithMosparo([
        'name' => 'Ada',
        'avatar' => UploadedFile::fake()->create('avatar.png'),
        'tags' => ['one', UploadedFile::fake()->create('other.png')],
        '_mosparo_submitToken' => 'submit',
        '_mosparo_validationToken' => 'validation',
    ])->passes();

    expect($requests[0]['formData'])->toBe([
        'name' => hash('sha256', 'Ada'),
        'tags' => [hash('sha256', 'one')],
    ]);
});

it('checks against the named project', function () {
    config()->set('mosparo.projects.newsletter', [
        'host' => 'https://mosparo.example',
        'uuid' => 'newsletter-uuid',
        'public_key' => 'newsletter-public',
        'private_key' => 'newsletter-private',
    ]);
    $requests = mosparoAnswers(project: 'newsletter');

    $validator = validateWithMosparo([
        '_mosparo_submitToken' => 'submit',
        '_mosparo_validationToken' => 'validation',
    ], 'newsletter');

    expect($validator->passes())->toBeTrue()
        ->and($requests)->toHaveCount(1);
});

it('lets everything through when switched off', function () {
    config()->set('mosparo.enabled', false);

    expect(validateWithMosparo([])->passes())->toBeTrue();
});
