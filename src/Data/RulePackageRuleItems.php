<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Mosparo\ApiClient\RulePackageRulesRuleItemsResult;

/**
 * One page of the items of a rule in a rule package.
 */
final readonly class RulePackageRuleItems
{
    /**
     * @param list<RulePackageRuleItem> $items
     */
    public function __construct(
        public bool $successful,
        public array $items,
        public int $page,
        public int $totalPages,
    ) {
    }

    public static function from(RulePackageRulesRuleItemsResult $result): self
    {
        return new self(
            $result->isSuccessful(),
            array_values(array_map(RulePackageRuleItem::from(...), $result->getRuleItems())),
            $result->getPage(),
            $result->getTotalPages(),
        );
    }

    public function hasMorePages(): bool
    {
        return $this->page < $this->totalPages;
    }
}
