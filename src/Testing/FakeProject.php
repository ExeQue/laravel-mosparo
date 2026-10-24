<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Testing;

use ExeQue\Mosparo\Project;

/**
 * A project answering every check with a fixed result, as long as both
 * tokens are there. Each check is recorded on the FakeMosparo.
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
        ], true);
    }

    public function passes(array $formData, mixed $submitToken, mixed $validationToken, array $fields): bool
    {
        $this->mosparo->checks[] = ['project' => $this->name, 'formData' => $formData, 'fields' => $fields];

        return is_string($submitToken) && $submitToken !== ''
            && is_string($validationToken) && $validationToken !== ''
            && $this->result;
    }
}
