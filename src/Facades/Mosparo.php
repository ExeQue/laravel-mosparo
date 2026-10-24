<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Facades;

use ExeQue\Mosparo\Mosparo as MosparoManager;
use ExeQue\Mosparo\Testing\FakeMosparo;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \ExeQue\Mosparo\Project project(?string $name = null)
 * @method static bool enabled()
 * @method static string host()
 * @method static string uuid()
 * @method static string publicKey()
 * @method static bool passes(array $formData, mixed $submitToken, mixed $validationToken, array $fields)
 * @method static \Mosparo\ApiClient\VerificationResult verify(array $formData, string $submitToken, string $validationToken)
 * @method static \Mosparo\ApiClient\Client client()
 * @method static \Illuminate\Support\HtmlString script()
 * @method static array frontend(array $options = [])
 *
 * @see MosparoManager
 * @see \ExeQue\Mosparo\Project
 */
class Mosparo extends Facade
{
    /**
     * Answers every check with $passes, without asking mosparo. Checks still
     * need both tokens; the recorded checks are on the returned fake.
     */
    public static function fake(bool $passes = true): FakeMosparo
    {
        $fake = new FakeMosparo($passes);

        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return MosparoManager::class;
    }
}
