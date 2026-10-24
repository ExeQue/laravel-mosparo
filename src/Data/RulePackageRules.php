<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Mosparo\ApiClient\RulePackageRulesResult;

/**
 * One page of the rules in a rule package.
 */
final readonly class RulePackageRules
{
    /**
     * @param list<RulePackageRule> $rules
     */
    public function __construct(
        public bool $successful,
        public array $rules,
        public int $page,
        public int $totalPages,
    ) {
    }

    public static function from(RulePackageRulesResult $result): self
    {
        return new self(
            $result->isSuccessful(),
            array_values(array_map(RulePackageRule::from(...), $result->getRules())),
            $result->getPage(),
            $result->getTotalPages(),
        );
    }

    public function hasMorePages(): bool
    {
        return $this->page < $this->totalPages;
    }
}
