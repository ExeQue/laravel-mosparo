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

    /**
     * @param array{successful: bool, rules: list<array<string, mixed>>, page: int, totalPages: int} $page
     */
    public static function fromArray(array $page): self
    {
        return new self($page['successful'], array_map(RulePackageRule::fromArray(...), $page['rules']), $page['page'], $page['totalPages']);
    }

    /**
     * @return array{successful: bool, rules: list<array<string, mixed>>, page: int, totalPages: int}
     */
    public function toArray(): array
    {
        return [
            'successful' => $this->successful,
            'rules' => array_map(fn (RulePackageRule $rule): array => $rule->toArray(), $this->rules),
            'page' => $this->page,
            'totalPages' => $this->totalPages,
        ];
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
