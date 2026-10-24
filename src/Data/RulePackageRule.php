<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Carbon\CarbonImmutable;
use Mosparo\ApiClient\RulePackageRule as Rule;

/**
 * One rule in a rule package.
 */
final readonly class RulePackageRule
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $type,
        public string $name,
        public string $description,
        public int $numberOfItems,
        public float $spamRatingFactor,
        public ?CarbonImmutable $updatedAt,
        public string $listRoute,
    ) {
    }

    public static function from(Rule $rule): self
    {
        return new self(
            $rule->getId(),
            $rule->getUuid(),
            $rule->getType(),
            $rule->getName(),
            $rule->getDescription(),
            $rule->getNumberOfItems(),
            $rule->getSpamRatingFactor(),
            $rule->getUpdatedAt() === null ? null : CarbonImmutable::instance($rule->getUpdatedAt()),
            $rule->getListRoute(),
        );
    }
}
