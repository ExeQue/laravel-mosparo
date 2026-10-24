<?php

declare(strict_types=1);

namespace ExeQue\Mosparo;

use ExeQue\Mosparo\Cache\ProjectCache;
use ExeQue\Mosparo\Exceptions\UnknownProject;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository;

/**
 * The configured mosparo projects. Methods of Project called on it go to the
 * default project: `Mosparo::passes(...)` is `Mosparo::project()->passes(...)`.
 *
 * @mixin Project
 */
class Mosparo
{
    /**
     * Dispatched by the box to its Livewire component, with the tokens of its check.
     */
    public const string CHECKED_EVENT = 'mosparo-checked';

    /**
     * Dispatched once a check has spent the tokens; the boxes of that Livewire
     * component fetch a new submit token.
     */
    public const string RESET_EVENT = 'mosparo-reset';

    public function __construct(
        private readonly Repository $config,
        private readonly CacheFactory $caches,
    ) {
    }

    /**
     * A configured project, or the default one. The shared `host`,
     * `client_arguments` and `cache` apply where the project sets none of its own.
     *
     * @throws UnknownProject When no project of that name is configured.
     */
    public function project(?string $name = null): Project
    {
        $name ??= (string) $this->config->get('mosparo.default', 'default');
        $config = $this->config->get("mosparo.projects.{$name}");

        if (! is_array($config)) {
            throw UnknownProject::named($name);
        }

        $config['host'] = ($config['host'] ?? null) ?: $this->config->get('mosparo.host');
        $config['client_arguments'] = array_replace(
            (array) $this->config->get('mosparo.client_arguments', []),
            (array) ($config['client_arguments'] ?? []),
        );

        $cache = array_replace((array) $this->config->get('mosparo.cache', []), (array) ($config['cache'] ?? []));

        return new Project($name, $config, (bool) $this->config->get('mosparo.enabled'), new ProjectCache(
            $name,
            (bool) ($cache['enabled'] ?? false),
            $this->caches->store($cache['store'] ?? null),
            $this->generations(),
            (int) ($cache['ttl'] ?? 300),
        ));
    }

    /**
     * Clears what was cached from mosparo: for one project, or for every
     * project when none is named. Nothing else in the cache is touched.
     *
     * @throws UnknownProject When no project of that name is configured.
     */
    public function flushCache(?string $project = null): void
    {
        if ($project !== null) {
            $this->project($project)->flushCache();

            return;
        }

        ProjectCache::flushAll($this->generations());
    }

    /**
     * The store keeping the cache generations: the shared cache store.
     */
    private function generations(): Cache
    {
        return $this->caches->store($this->config->get('mosparo.cache.store'));
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->project()->{$method}(...$arguments);
    }
}
