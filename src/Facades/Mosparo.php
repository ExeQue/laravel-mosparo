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
 * @method static \ExeQue\Mosparo\Data\Verification verify(array $formData, string $submitToken, string $validationToken)
 * @method static \Mosparo\ApiClient\Client client()
 * @method static \Illuminate\Support\HtmlString script()
 * @method static array frontend(array $options = [])
 * @method static \ExeQue\Mosparo\Project withCache(?int $ttl = null)
 * @method static \ExeQue\Mosparo\Project withoutCache()
 * @method static void flushCache(?string $project = null)
 * @method static bool storeMetadata(string $submitToken, string $validationToken, array $metadata)
 * @method static \ExeQue\Mosparo\Data\Statistics statistics(int $days = 0, ?\DateTimeInterface $from = null)
 * @method static \ExeQue\Mosparo\Data\RulePackageRules rulePackageRules(int $rulePackageId, int $page = 1, int $perPage = 1000)
 * @method static \ExeQue\Mosparo\Data\RulePackageRuleItems rulePackageRuleItems(int $rulePackageId, int $ruleId, int $page = 1, int $perPage = 1000)
 * @method static bool streamRulePackageHashIndex(int $rulePackageId, \SplFileObject $target, ?callable $progress = null)
 * @method static \ExeQue\Mosparo\Data\RulePackageImport importRulePackage(int $rulePackageId, string $content, string $hash)
 * @method static \ExeQue\Mosparo\Data\RulePackageUpdate updateRulePackage(int $rulePackageId, array $tasks)
 *
 * @see MosparoManager
 * @see \ExeQue\Mosparo\Project
 */
class Mosparo extends Facade
{
    /**
     * Answers every check without asking mosparo: accepted, until
     * `reject()` is called on the returned fake. Checks still need both
     * tokens; the fake records them and offers assertions.
     */
    public static function fake(): FakeMosparo
    {
        $fake = new FakeMosparo();

        static::swap($fake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return MosparoManager::class;
    }
}
