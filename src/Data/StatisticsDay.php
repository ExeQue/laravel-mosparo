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
