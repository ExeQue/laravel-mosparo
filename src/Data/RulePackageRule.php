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

    /**
     * @param array{id: int, uuid: string, type: string, name: string, description: string, numberOfItems: int, spamRatingFactor: float, updatedAt: ?string, listRoute: string} $rule
     */
    public static function fromArray(array $rule): self
    {
        return new self(
            $rule['id'],
            $rule['uuid'],
            $rule['type'],
            $rule['name'],
            $rule['description'],
            $rule['numberOfItems'],
            $rule['spamRatingFactor'],
            $rule['updatedAt'] === null ? null : CarbonImmutable::parse($rule['updatedAt']),
            $rule['listRoute'],
        );
    }

    /**
     * @return array{id: int, uuid: string, type: string, name: string, description: string, numberOfItems: int, spamRatingFactor: float, updatedAt: ?string, listRoute: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'numberOfItems' => $this->numberOfItems,
            'spamRatingFactor' => $this->spamRatingFactor,
            'updatedAt' => $this->updatedAt?->toIso8601String(),
            'listRoute' => $this->listRoute,
        ];
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
