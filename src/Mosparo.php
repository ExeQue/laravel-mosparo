<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

/**
 * The configured mosparo projects. Methods of Project called on it go to the
 * default project: `Mosparo::passes(...)` is `Mosparo::project()->passes(...)`.
 *
 * @mixin Project
 */
class Mosparo
{
    /**
     * Dispatched once a check has spent the tokens; the box answers by
     * fetching a new submit token.
     */
    public const string RESET_EVENT = 'mosparo-reset';

    public function __construct(
        private readonly Repository $config,
    ) {
    }

    /**
     * A configured project, or the default one. A project without a host of
     * its own uses the shared `mosparo.host`.
     *
     * @throws InvalidArgumentException When no project of that name is configured.
     */
    public function project(?string $name = null): Project
    {
        $name ??= (string) $this->config->get('mosparo.default', 'default');

        $config = $this->config->get("mosparo.projects.{$name}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("The mosparo project [{$name}] is not configured.");
        }

        $config['host'] = ($config['host'] ?? null) ?: $this->config->get('mosparo.host');

        return new Project($name, $config, (bool) $this->config->get('mosparo.enabled'));
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->project()->{$method}(...$arguments);
    }
}
