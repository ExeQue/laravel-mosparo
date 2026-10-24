<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Testing;

use Closure;
use ExeQue\Mosparo\Data\RulePackageRuleItems;
use ExeQue\Mosparo\Data\RulePackageRules;
use ExeQue\Mosparo\Data\Statistics;
use ExeQue\Mosparo\Exceptions\UnknownProject;
use ExeQue\Mosparo\Mosparo;
use ExeQue\Mosparo\Project;
use PHPUnit\Framework\Assert;

/**
 * Stands in for mosparo in an application's tests: every project is enabled,
 * renders a working box, accepts or rejects checks as told, and reads the
 * data it was given, all without asking mosparo. Installed by `Mosparo::fake()`.
 */
class FakeMosparo extends Mosparo
{
    /**
     * @var list<array{project: string, formData: array<array-key, mixed>, fields: list<string>, passed: bool}>
     */
    public array $checks = [];

    /**
     * @var list<array{project: string, metadata: array<string, mixed>}>
     */
    public array $metadata = [];

    /**
     * @var list<array{project: string, rulePackageId: int, change: string}>
     */
    public array $rulePackageChanges = [];

    private bool $accepts = true;

    private ?Statistics $statistics = null;

    /**
     * @var array<string, RulePackageRules|RulePackageRuleItems>
     */
    private array $rulePackages = [];

    public function __construct()
    {
        parent::__construct(app('config'), app('cache'));
    }

    /**
     * Accepts every check that has both tokens. The default.
     */
    public function accept(): self
    {
        $this->accepts = true;

        return $this;
    }

    /**
     * Rejects every check, as mosparo does with spam.
     */
    public function reject(): self
    {
        $this->accepts = false;

        return $this;
    }

    /**
     * @internal Whether a FakeProject accepts a check.
     */
    public function accepts(): bool
    {
        return $this->accepts;
    }

    public function project(?string $name = null): Project
    {
        $name ??= (string) config('mosparo.default', 'default');

        if (! is_array(config("mosparo.projects.{$name}"))) {
            throw UnknownProject::named($name);
        }

        return new FakeProject($name, $this);
    }

    /**
     * What `statistics()` answers, for every project.
     */
    public function withStatistics(Statistics $statistics): self
    {
        $this->statistics = $statistics;

        return $this;
    }

    /**
     * What `rulePackageRules()` answers for one rule package, on every page.
     */
    public function withRulePackageRules(int $rulePackageId, RulePackageRules $rules): self
    {
        $this->rulePackages["{$rulePackageId}"] = $rules;

        return $this;
    }

    /**
     * What `rulePackageRuleItems()` answers for one rule, on every page.
     */
    public function withRulePackageRuleItems(int $rulePackageId, int $ruleId, RulePackageRuleItems $items): self
    {
        $this->rulePackages["{$rulePackageId}:{$ruleId}"] = $items;

        return $this;
    }

    /**
     * @internal What a FakeProject answers.
     */
    public function statisticsFor(): Statistics
    {
        return $this->statistics ?? new Statistics(0, 0, null, []);
    }

    /**
     * @internal What a FakeProject answers.
     */
    public function rulesFor(int $rulePackageId): RulePackageRules
    {
        $rules = $this->rulePackages["{$rulePackageId}"] ?? null;

        return $rules instanceof RulePackageRules ? $rules : new RulePackageRules(true, [], 1, 1);
    }

    /**
     * @internal What a FakeProject answers.
     */
    public function ruleItemsFor(int $rulePackageId, int $ruleId): RulePackageRuleItems
    {
        $items = $this->rulePackages["{$rulePackageId}:{$ruleId}"] ?? null;

        return $items instanceof RulePackageRuleItems ? $items : new RulePackageRuleItems(true, [], 1, 1);
    }

    /**
     * Asserts that a submission was checked; one the callback accepts, when given.
     *
     * @param (Closure(array{project: string, formData: array<array-key, mixed>, fields: list<string>, passed: bool}): bool)|null $callback
     */
    public function assertChecked(?Closure $callback = null): self
    {
        Assert::assertNotEmpty(
            array_filter($this->checks, $callback ?? fn (): bool => true),
            $callback === null ? 'No submission was checked with mosparo.' : 'No checked submission matched the callback.',
        );

        return $this;
    }

    public function assertNotChecked(): self
    {
        Assert::assertEmpty($this->checks, 'A submission was checked with mosparo.');

        return $this;
    }

    /**
     * Asserts that metadata was stored; this metadata, when given.
     *
     * @param array<string, mixed>|null $metadata
     */
    public function assertMetadataStored(?array $metadata = null): self
    {
        $stored = array_column($this->metadata, 'metadata');

        $metadata === null
            ? Assert::assertNotEmpty($stored, 'No metadata was stored with mosparo.')
            : Assert::assertContains($metadata, $stored, 'This metadata was not stored with mosparo.');

        return $this;
    }
}
