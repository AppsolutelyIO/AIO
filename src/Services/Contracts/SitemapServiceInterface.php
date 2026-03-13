<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface SitemapServiceInterface
{
    /**
     * Generate XML sitemap index with caching
     * This is the main sitemap.xml that references sub-sitemaps
     */
    public function generateXml(): string;

    /**
     * Generate page sitemap XML with caching
     */
    public function generatePageXml(): string;

    /**
     * Generate article sitemap XML with caching
     */
    public function generateArticleXml(): string;

    /**
     * Generate product sitemap XML with caching
     */
    public function generateProductXml(): string;

    /**
     * Generate sitemap XML by type (page, article, or product) with caching
     */
    public function generateTypeXml(string $type): string;

    /**
     * Clear all sitemap cache (call after updates)
     */
    public function clearCache(): void;
}
