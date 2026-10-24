<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Facades;

use ExeQue\Mosparo\Mosparo as MosparoManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ExeQue\Mosparo\Project project(?string $name = null)
 * @method static bool enabled()
 * @method static string host()
 * @method static string uuid()
 * @method static string publicKey()
 * @method static bool passes(array $formData, ?string $submitToken, ?string $validationToken)
 * @method static \Mosparo\ApiClient\VerificationResult verify(array $formData, string $submitToken, string $validationToken)
 * @method static \Mosparo\ApiClient\Client client()
 * @method static \Illuminate\Support\HtmlString script()
 *
 * @see MosparoManager
 * @see \ExeQue\Mosparo\Project
 */
class Mosparo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MosparoManager::class;
    }
}
