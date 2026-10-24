<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Testing;

use ExeQue\Mosparo\Mosparo;
use ExeQue\Mosparo\Project;

/**
 * Stands in for mosparo in an application's tests: every project is enabled,
 * renders a working box, and answers checks with the given result without
 * asking mosparo. Installed by `Mosparo::fake()`.
 */
class FakeMosparo extends Mosparo
{
    /**
     * @var list<array{project: string, formData: array<array-key, mixed>, fields: list<string>}>
     */
    public array $checks = [];

    public function __construct(
        private readonly bool $passes,
    ) {
        parent::__construct(app('config'));
    }

    public function project(?string $name = null): Project
    {
        return new FakeProject($name ?? (string) config('mosparo.default', 'default'), $this->passes, $this);
    }
}
