<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Mosparo\ApiClient\RulePackageImportResult;

/**
 * mosparo's answer to new content for a rule package.
 */
final readonly class RulePackageImport
{
    /**
     * @param bool $verifiedHash Whether the content matched the hash sent with it.
     */
    public function __construct(
        public bool $successful,
        public bool $verifiedHash,
    ) {
    }

    public static function from(RulePackageImportResult $result): self
    {
        return new self($result->isSuccessful(), $result->isHashValidated());
    }
}
