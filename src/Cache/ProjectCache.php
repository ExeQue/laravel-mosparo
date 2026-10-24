<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository;

/**
 * Caches what a project reads from mosparo. Clearing touches nothing but
 * these entries: every key carries a generation number, kept in the shared
 * cache store, and clearing moves the generation on. Entries of an older
 * generation are no longer read and expire with their TTL. This works on
 * every cache store, also those without tags.
 */
final class ProjectCache
{
    public const string GENERATION = 'mosparo:generation';

    /**
     * @param Repository $entries Where the project's entries are kept.
     * @param Repository $generations Where the generation numbers are kept, shared by every project.
     * @param int $ttl Seconds an entry is kept.
     */
    public function __construct(
        private readonly string $project,
        private readonly bool $enabled,
        private readonly Repository $entries,
        private readonly Repository $generations,
        private readonly int $ttl,
    ) {
    }

    /**
     * The same cache, switched on or off, and with another TTL when given.
     */
    public function with(bool $enabled, ?int $ttl = null): self
    {
        return new self($this->project, $enabled, $this->entries, $this->generations, $ttl ?? $this->ttl);
    }

    /**
     * The cached value, or the callback's result, kept for the TTL. Calls the
     * callback every time while caching is off.
     *
     * @template T
     *
     * @param Closure(): T $callback
     *
     * @return T
     */
    public function remember(string $key, Closure $callback): mixed
    {
        if (! $this->enabled) {
            return $callback();
        }

        return $this->entries->remember($this->key($key), $this->ttl, $callback);
    }

    /**
     * Clears this project's entries.
     */
    public function flush(): void
    {
        self::advance($this->generations, self::projectGeneration($this->project));
    }

    /**
     * Clears the entries of every project.
     */
    public static function flushAll(Repository $generations): void
    {
        self::advance($generations, self::GENERATION);
    }

    private function key(string $key): string
    {
        return sprintf(
            'mosparo:%d:%s:%d:%s',
            (int) $this->generations->get(self::GENERATION, 0),
            $this->project,
            (int) $this->generations->get(self::projectGeneration($this->project), 0),
            $key,
        );
    }

    private static function projectGeneration(string $project): string
    {
        return "mosparo:{$project}:generation";
    }

    private static function advance(Repository $generations, string $key): void
    {
        $generations->add($key, 0);
        $generations->increment($key);
    }
}
