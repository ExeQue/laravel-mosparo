<?php

declare(strict_types=1);

namespace ExeQue\Mosparo\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;

/**
 * Caches what a project reads from mosparo, as plain arrays. Clearing the
 * cache, for one project or for all of them, affects only these entries:
 * every key carries the current generation of its project and of the whole
 * cache, and clearing gives that generation a new value.
 */
final class ProjectCache
{
    public const string GENERATION = 'mosparo:generation';

    /**
     * @param Repository $entries Where the project's entries are kept.
     * @param Repository $generations Where the generations are kept, shared by every project.
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
     * The cached value, or the callback's result. A result is kept for the
     * TTL unless $keep turns it down. Calls the callback every time while
     * caching is off.
     *
     * @param Closure(): array<array-key, mixed> $callback
     * @param (Closure(array<array-key, mixed>): bool)|null $keep
     *
     * @return array<array-key, mixed>
     */
    public function remember(string $key, Closure $callback, ?Closure $keep = null): array
    {
        if (! $this->enabled) {
            return $callback();
        }

        $key = $this->key($key);
        $cached = $this->entries->get($key);

        if (is_array($cached)) {
            return $cached;
        }

        $value = $callback();

        if ($keep === null || $keep($value)) {
            $this->entries->put($key, $value, $this->ttl);
        }

        return $value;
    }

    /**
     * Clears this project's entries.
     */
    public function flush(): void
    {
        self::renew($this->generations, self::projectGeneration($this->project));
    }

    /**
     * Clears the entries of every project.
     */
    public static function flushAll(Repository $generations): void
    {
        self::renew($generations, self::GENERATION);
    }

    private function key(string $key): string
    {
        return sprintf(
            'mosparo:%s:%s:%s:%s',
            (string) $this->generations->get(self::GENERATION, '0'),
            $this->project,
            (string) $this->generations->get(self::projectGeneration($this->project), '0'),
            $key,
        );
    }

    private static function projectGeneration(string $project): string
    {
        return "mosparo:{$project}:generation";
    }

    private static function renew(Repository $generations, string $key): void
    {
        $generations->forever($key, Str::random(16));
    }
}
