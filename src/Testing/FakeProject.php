<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Testing;

use DateTimeInterface;
use ExeQue\Mosparo\Cache\ProjectCache;
use ExeQue\Mosparo\Data\RulePackageImport;
use ExeQue\Mosparo\Data\RulePackageRuleItems;
use ExeQue\Mosparo\Data\RulePackageRules;
use ExeQue\Mosparo\Data\RulePackageUpdate;
use ExeQue\Mosparo\Data\Statistics;
use ExeQue\Mosparo\Data\Verification;
use ExeQue\Mosparo\Enums\FieldStatus;
use ExeQue\Mosparo\Project;
use SplFileObject;

/**
 * A project answering from its FakeMosparo: checks with a fixed result, as
 * long as both tokens are there, and data with what the fake was given.
 * Everything it is asked is recorded on the fake.
 */
class FakeProject extends Project
{
    public function __construct(
        string $name,
        private readonly bool $result,
        private readonly FakeMosparo $mosparo,
    ) {
        parent::__construct($name, [
            'host' => 'https://mosparo.invalid',
            'uuid' => "fake-{$name}",
            'public_key' => "fake-{$name}-public",
            'private_key' => "fake-{$name}-private",
        ], true, new ProjectCache($name, false, app('cache')->store(), app('cache')->store(), 0));
    }

    public function withoutCache(): static
    {
        return $this;
    }

    public function withCache(?int $ttl = null): static
    {
        return $this;
    }

    public function flushCache(): void
    {
    }

    public function passes(array $formData, mixed $submitToken, mixed $validationToken, array $fields): bool
    {
        $passed = is_string($submitToken) && $submitToken !== ''
            && is_string($validationToken) && $validationToken !== ''
            && $this->result;

        $this->mosparo->checks[] = ['project' => $this->name, 'formData' => $formData, 'fields' => $fields, 'passed' => $passed];

        return $passed;
    }

    public function verify(array $formData, string $submitToken, string $validationToken): Verification
    {
        $status = $this->result ? FieldStatus::Valid : FieldStatus::Invalid;

        return new Verification(
            $this->result,
            $this->result,
            array_map(fn (): FieldStatus => $status, $formData),
            [],
            null,
        );
    }

    public function storeMetadata(string $submitToken, string $validationToken, array $metadata): bool
    {
        $this->mosparo->metadata[] = ['project' => $this->name, 'metadata' => $metadata];

        return true;
    }

    public function statistics(int $days = 0, ?DateTimeInterface $from = null): Statistics
    {
        return $this->mosparo->statisticsFor();
    }

    public function rulePackageRules(int $rulePackageId, int $page = 1, int $perPage = 1000): RulePackageRules
    {
        return $this->mosparo->rulesFor($rulePackageId);
    }

    public function rulePackageRuleItems(int $rulePackageId, int $ruleId, int $page = 1, int $perPage = 1000): RulePackageRuleItems
    {
        return $this->mosparo->ruleItemsFor($rulePackageId, $ruleId);
    }

    public function streamRulePackageHashIndex(int $rulePackageId, SplFileObject $target, ?callable $progress = null): bool
    {
        return true;
    }

    public function importRulePackage(int $rulePackageId, string $content, string $hash): RulePackageImport
    {
        $this->mosparo->rulePackageChanges[] = ['project' => $this->name, 'rulePackageId' => $rulePackageId, 'change' => 'import'];

        return new RulePackageImport(true, true);
    }

    public function updateRulePackage(int $rulePackageId, array $tasks): RulePackageUpdate
    {
        $this->mosparo->rulePackageChanges[] = ['project' => $this->name, 'rulePackageId' => $rulePackageId, 'change' => 'update'];

        return new RulePackageUpdate(true, []);
    }
}
