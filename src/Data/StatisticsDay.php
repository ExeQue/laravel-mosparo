<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Carbon\CarbonImmutable;

/**
 * Submissions to one project on one day.
 */
final readonly class StatisticsDay
{
    public function __construct(
        public CarbonImmutable $date,
        public int $validSubmissions,
        public int $spamSubmissions,
        public int $delayedRequests,
        public int $blockedRequests,
    ) {
    }

    /**
     * @param array{date: string, validSubmissions: int, spamSubmissions: int, delayedRequests: int, blockedRequests: int} $day
     */
    public static function fromArray(array $day): self
    {
        return new self(
            CarbonImmutable::parse($day['date']),
            $day['validSubmissions'],
            $day['spamSubmissions'],
            $day['delayedRequests'],
            $day['blockedRequests'],
        );
    }

    /**
     * @return array{date: string, validSubmissions: int, spamSubmissions: int, delayedRequests: int, blockedRequests: int}
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date->toDateString(),
            'validSubmissions' => $this->validSubmissions,
            'spamSubmissions' => $this->spamSubmissions,
            'delayedRequests' => $this->delayedRequests,
            'blockedRequests' => $this->blockedRequests,
        ];
    }

    /**
     * @param array<string, mixed> $numbers
     */
    public static function from(string $date, array $numbers): self
    {
        return new self(
            CarbonImmutable::parse($date)->startOfDay(),
            (int) ($numbers['numberOfValidSubmissions'] ?? 0),
            (int) ($numbers['numberOfSpamSubmissions'] ?? 0),
            (int) ($numbers['numberOfDelayedRequests'] ?? 0),
            (int) ($numbers['numberOfBlockedRequests'] ?? 0),
        );
    }
}
