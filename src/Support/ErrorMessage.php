<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Support;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * The spam check's error, wherever it was put: on the tokens of a Livewire
 * component's MosparoForm, or on the fields of a plain form post.
 */
final class ErrorMessage
{
    public const array KEYS = [
        'mosparo.submitToken',
        'mosparo.validationToken',
        '_mosparo_submitToken',
        '_mosparo_validationToken',
    ];

    /**
     * @param mixed $errors The view's `$errors`, if it has one.
     */
    public static function from(mixed $errors): ?string
    {
        if ($errors instanceof ViewErrorBag) {
            $errors = $errors->getBag('default');
        }

        if (! $errors instanceof MessageBag) {
            return null;
        }

        foreach (self::KEYS as $key) {
            if ($errors->has($key)) {
                return $errors->first($key);
            }
        }

        return null;
    }
}
