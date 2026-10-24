<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Mosparo\ApiClient\RulePackageBatchResult;

/**
 * mosparo's answer to a batch of changes to a rule package.
 */
final readonly class RulePackageUpdate
{
    /**
     * @param list<mixed> $errors What went wrong, task by task.
     */
    public function __construct(
        public bool $successful,
        public array $errors,
    ) {
    }

    public static function from(RulePackageBatchResult $result): self
    {
        return new self($result->isSuccessful(), array_values($result->getErrors()));
    }
}
