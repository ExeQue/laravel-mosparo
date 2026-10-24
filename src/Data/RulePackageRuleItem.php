<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Mosparo\ApiClient\RulePackageRuleItem as RuleItem;

/**
 * One item of a rule in a rule package, such as a word or an address.
 */
final readonly class RulePackageRuleItem
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $type,
        public string $value,
        public float $rating,
    ) {
    }

    /**
     * @param array{id: int, uuid: string, type: string, value: string, rating: float} $item
     */
    public static function fromArray(array $item): self
    {
        return new self($item['id'], $item['uuid'], $item['type'], $item['value'], $item['rating']);
    }

    /**
     * @return array{id: int, uuid: string, type: string, value: string, rating: float}
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'uuid' => $this->uuid, 'type' => $this->type, 'value' => $this->value, 'rating' => $this->rating];
    }

    public static function from(RuleItem $item): self
    {
        return new self($item->getId(), $item->getUuid(), $item->getType(), $item->getValue(), $item->getRating());
    }
}
