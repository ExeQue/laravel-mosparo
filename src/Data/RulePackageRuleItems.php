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

    /**
     * @param array{successful: bool, items: list<array<string, mixed>>, page: int, totalPages: int} $page
     */
    public static function fromArray(array $page): self
    {
        return new self($page['successful'], array_map(RulePackageRuleItem::fromArray(...), $page['items']), $page['page'], $page['totalPages']);
    }

    /**
     * @return array{successful: bool, items: list<array<string, mixed>>, page: int, totalPages: int}
     */
    public function toArray(): array
    {
        return [
            'successful' => $this->successful,
            'items' => array_map(fn (RulePackageRuleItem $item): array => $item->toArray(), $this->items),
            'page' => $this->page,
            'totalPages' => $this->totalPages,
        ];
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
