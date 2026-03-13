<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;

interface GeneralPageServiceInterface
{
    /**
     * Resolve page with caching
     */
    public function resolvePageWithCaching(?string $slug): ?GeneralPage;

    /**
     * Resolve page by slug
     */
    public function resolvePage(string $fullSlug): ?GeneralPage;

    /**
     * Resolve nested URL
     */
    public function resolveNestedUrl(string $fullSlug): ?GeneralPage;

    /**
     * Get page hierarchy for breadcrumbs
     */
    public function getPageHierarchy(GeneralPage $page): array;

    /**
     * Get published page by ID
     */
    public function getPublishedPageById(int $id): ?Page;

    /**
     * Clear page cache for a specific slug
     */
    public function clearPageCache(string $slug): void;

    /**
     * Clear all page cache
     */
    public function clearAllPageCache(): void;

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array;

    /**
     * Add or update a nested content mapping in blocks config
     */
    public function addBlockMapping(string $blockClass, array $repositories): void;

    /**
     * Get all block mappings
     */
    public function getBlockMappings(): array;

    /**
     * Check if a block class has repository mappings
     */
    public function hasBlockMapping(string $blockClass): bool;
}
