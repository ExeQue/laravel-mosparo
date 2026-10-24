<?php

declare(strict_types=1);

it('says the check failed in every language it ships', function (string $locale) {
    $messages = require dirname(__DIR__, 2)."/lang/{$locale}/messages.php";

    expect(array_keys($messages))->toBe(array_keys(require dirname(__DIR__, 2).'/lang/en/messages.php'))
        ->and($messages['failed'])->toBeString()->not->toBeEmpty();
})->with(fn (): array => array_map('basename', glob(dirname(__DIR__, 2).'/lang/*', GLOB_ONLYDIR)));

it('answers in the application locale', function () {
    app()->setLocale('de');

    expect(__('mosparo::messages.failed'))->toBe('Die Spam-Prüfung ist fehlgeschlagen. Bitte versuchen Sie es erneut.');
});
