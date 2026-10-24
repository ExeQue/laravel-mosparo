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
