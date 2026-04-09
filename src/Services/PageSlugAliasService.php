<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Resolves page slug aliases (e.g. thank-you-for-submitting -> test-drive).
 *
 * Aliases are cached on demand and fall back to a database lookup when the
 * cache is empty (e.g. after cache expiry, cold start, or deployment).
 */
final readonly class PageSlugAliasService
{
    private const CACHE_KEY = 'page_slug_aliases';

    private const CACHE_TTL = 3600;

    public function __construct(
        private CacheRepository $cache,
        private PageBlockSettingRepository $blockSettingRepository
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
     * Falls back to database when cache misses.
     */
    public function resolveAlias(string $normalizedSlug): ?string
    {
        $aliases = $this->getAliases();

        if (isset($aliases[$normalizedSlug])) {
            return $aliases[$normalizedSlug];
        }

        // Cache miss — resolve from database and warm cache
        return $this->resolveFromDatabase($normalizedSlug);
    }

    /**
     * Clear the alias cache. Call when pages or block settings change.
     */
    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    /**
     * Look up a redirect_url alias directly from page block display_options.
     * Called when cache is empty (expired, cold start, post-deploy).
     */
    private function resolveFromDatabase(string $normalizedSlug): ?string
    {
        try {
            $bare    = ltrim($normalizedSlug, '/');
            $setting = $this->blockSettingRepository->findByForceRedirectUrl([
                $normalizedSlug,
                $bare,
                '/' . $bare,
            ]);

            /** @var Page|null $page */
            $page = $setting?->page;

            if (! $page) {
                return null;
            }

            $canonicalSlug = normalize_slug($page->getAttribute('slug'));

            // Warm cache for subsequent requests
            $this->addAlias($normalizedSlug, $canonicalSlug);

            return $canonicalSlug;
        } catch (\Throwable) {
            return null;
        }
    }
}
