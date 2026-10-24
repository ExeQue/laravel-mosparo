<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Data;

use ExeQue\Mosparo\Enums\FieldStatus;
use Mosparo\ApiClient\VerificationResult;

/**
 * mosparo's answer about one submission.
 */
final readonly class Verification
{
    /**
     * @param bool $submittable Valid, and signed with the project's private key.
     * @param bool $valid What mosparo answered, before the signature is checked.
     * @param array<string, FieldStatus> $fields The status of every field mosparo checked.
     * @param list<array<string, mixed>> $issues What mosparo found wrong.
     * @param array<string, mixed>|null $debugInformation Details mosparo adds in its debug mode.
     */
    public function __construct(
        public bool $submittable,
        public bool $valid,
        public array $fields,
        public array $issues,
        public ?array $debugInformation,
    ) {
    }

    public static function from(VerificationResult $result): self
    {
        return new self(
            $result->isSubmittable(),
            $result->isValid(),
            array_map(
                fn (string $status): FieldStatus => FieldStatus::tryFrom($status) ?? FieldStatus::Invalid,
                $result->getVerifiedFields(),
            ),
            array_values($result->getIssues()),
            $result->getDebugInformation(),
        );
    }

    /**
     * The status of one field; not verified when mosparo did not check it.
     */
    public function field(string $name): FieldStatus
    {
        return $this->fields[$name] ?? FieldStatus::NotVerified;
    }
}
