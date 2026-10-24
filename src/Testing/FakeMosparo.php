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

    /**
     * @var Closure(array<array-key, mixed>, string): bool
     */
    private Closure $decision;

    /**
     * The outcomes of the next checks, used before $decision.
     *
     * @var list<bool>
     */
    private array $upcoming = [];

    private ?Statistics $statistics = null;

    /**
     * @var array<string, RulePackageRules|RulePackageRuleItems>
     */
    private array $rulePackages = [];

    public function __construct()
    {
        parent::__construct(app('config'), app('cache'));

        $this->accept();
    }

    /**
     * Accepts every check that has both tokens - the default.
     *
     * - With a callback, accepts the checks it returns true for and rejects the rest.
     * - With a number, accepts that many checks and rejects the ones after.
     *
     * @param (Closure(array<array-key, mixed> $formData, string $project): bool)|int|null $when
     */
    public function accept(Closure|int|null $when = null): self
    {
        if (is_int($when)) {
            return $this->reject()->sequence(...array_fill(0, max(0, $when), true));
        }

        $this->upcoming = [];
        $this->decision = fn (array $formData, string $project): bool => $when === null || (bool) $when($formData, $project);

        return $this;
    }

    /**
     * Rejects every check, as mosparo does with spam.
     *
     * - With a callback, rejects the checks it returns true for and accepts the rest.
     * - With a number, rejects that many checks and accepts the ones after.
     *
     * @param (Closure(array<array-key, mixed> $formData, string $project): bool)|int|null $when
     */
    public function reject(Closure|int|null $when = null): self
    {
        if (is_int($when)) {
            return $this->accept()->sequence(...array_fill(0, max(0, $when), false));
        }

        $this->upcoming = [];
        $this->decision = fn (array $formData, string $project): bool => $when !== null && ! $when($formData, $project);

        return $this;
    }

    /**
     * Decides the next checks in order - true accepts, false rejects -
     * after which the checks are decided as before.
     */
    public function sequence(bool ...$outcomes): self
    {
        $this->upcoming = array_values($outcomes);

        return $this;
    }

    /**
     * @internal Whether a FakeProject accepts a check.
     *
     * @param array<array-key, mixed> $formData
     */
    public function accepts(array $formData, string $project): bool
    {
        return array_shift($this->upcoming) ?? ($this->decision)($formData, $project);
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
