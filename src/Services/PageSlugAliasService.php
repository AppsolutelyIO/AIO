<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Caches page_slug_aliases on demand. When a user visits a form page with redirect='force',
 * that alias is added to the cache. Lookup resolves alias -> canonical slug.
 */
final readonly class PageSlugAliasService
{
    private const CACHE_KEY = 'page_slug_aliases';

    private const CACHE_TTL = 3600;

    public function __construct(
        private CacheRepository $cache
    ) {}

    /**
     * Add a single alias to the cache. Call when Dynamic Form (submitted=false) loads.
     */
    public function addAlias(string $aliasSlug, string $canonicalSlug): void
    {
        $aliasNorm     = normalize_slug($aliasSlug);
        $canonicalNorm = normalize_slug($canonicalSlug);

        if ($aliasNorm === '' || $aliasNorm === '/' || $canonicalNorm === '' || $canonicalNorm === '/' || $aliasNorm === $canonicalNorm) {
            return;
        }

        $aliases = $this->getAliases();
        if (($aliases[$aliasNorm] ?? null) === $canonicalNorm) {
            return;
        }

        $aliases[$aliasNorm] = $canonicalNorm;
        $this->cache->put(self::CACHE_KEY, $aliases, self::CACHE_TTL);
    }

    /**
     * Get alias -> canonical slug mapping from cache.
     *
     * @return array<string, string>
     */
    public function getAliases(): array
    {
        return $this->cache->get(self::CACHE_KEY, []);
    }

    /**
     * Resolve a slug via aliases. Returns canonical slug if the given slug is an alias.
     */
    public function resolveAlias(string $normalizedSlug): ?string
    {
        $aliases = $this->getAliases();

        return $aliases[$normalizedSlug] ?? null;
    }

    /**
     * Clear the alias cache. Call when pages or block settings change.
     */
    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }
}
