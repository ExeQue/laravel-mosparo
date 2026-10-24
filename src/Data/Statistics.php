<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use Carbon\CarbonImmutable;
use Mosparo\ApiClient\StatisticResult;

/**
 * Submissions to one project over a period.
 */
final readonly class Statistics
{
    /**
     * @param array<string, StatisticsDay> $days Keyed by date, `Y-m-d`.
     */
    public function __construct(
        public int $validSubmissions,
        public int $spamSubmissions,
        public ?CarbonImmutable $lastSubmissionAt,
        public array $days,
    ) {
    }

    /**
     * @param array{validSubmissions: int, spamSubmissions: int, lastSubmissionAt: ?string, days: array<string, array{date: string, validSubmissions: int, spamSubmissions: int, delayedRequests: int, blockedRequests: int}>} $statistics
     */
    public static function fromArray(array $statistics): self
    {
        return new self(
            $statistics['validSubmissions'],
            $statistics['spamSubmissions'],
            $statistics['lastSubmissionAt'] === null ? null : CarbonImmutable::parse($statistics['lastSubmissionAt']),
            array_map(StatisticsDay::fromArray(...), $statistics['days']),
        );
    }

    /**
     * @return array{validSubmissions: int, spamSubmissions: int, lastSubmissionAt: ?string, days: array<string, array{date: string, validSubmissions: int, spamSubmissions: int, delayedRequests: int, blockedRequests: int}>}
     */
    public function toArray(): array
    {
        return [
            'validSubmissions' => $this->validSubmissions,
            'spamSubmissions' => $this->spamSubmissions,
            'lastSubmissionAt' => $this->lastSubmissionAt?->toIso8601String(),
            'days' => array_map(fn (StatisticsDay $day): array => $day->toArray(), $this->days),
        ];
    }

    public static function from(StatisticResult $result): self
    {
        $days = [];

        foreach ($result->getNumbersByDate() as $date => $numbers) {
            $days[$date] = StatisticsDay::from((string) $date, (array) $numbers);
        }

        return new self(
            $result->getNumberOfValidSubmissions(),
            $result->getNumberOfSpamSubmissions(),
            $result->getLastSubmissionAt() === null ? null : CarbonImmutable::instance($result->getLastSubmissionAt()),
            $days,
        );
    }
}
