<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\Product;
use Illuminate\Support\Carbon;

/**
 * Service for building sitemap URL entries
 *
 * Handles URL entry preparation, hreflang generation, and metadata calculation.
 * Separated from SitemapService for better single responsibility.
 */
final readonly class SitemapBuilderService
{
    /**
     * Prepare URL entry data for Blade template
     */
    public function prepareUrlEntry(
        string $slug,
        Carbon $lastMod,
        string $changeFreq,
        float $priority,
        array $supportedLocales,
        string $defaultLocale,
        string $baseUrl
    ): array {
        $slug = normalize_slug($slug, false);
        // For home page (empty slug), don't add trailing slash
        $loc = $slug === '' ? $baseUrl : $baseUrl . '/' . $slug;

        $entry = [
            'loc'        => htmlspecialchars($loc, ENT_XML1, 'UTF-8'),
            'lastmod'    => $lastMod->toAtomString(),
            'changefreq' => $changeFreq,
            'priority'   => number_format($priority, 1, '.', ''),
            'hreflang'   => [],
        ];

        // Add hreflang alternatives if multi-language (more than 1 locale)
        if (count($supportedLocales) > 1) {
            $entry['hreflang'] = $this->prepareHreflangEntries($slug, $supportedLocales, $defaultLocale, $baseUrl);
        }

        return $entry;
    }

    /**
     * Prepare hreflang alternative links data for Blade template
     */
    public function prepareHreflangEntries(
        string $slug,
        array $supportedLocales,
        string $defaultLocale,
        string $baseUrl
    ): array {
        $hreflang = [];

        // Add x-default pointing to default locale
        $defaultSlug = ($slug === '') ? '' : '/' . $slug;
        $defaultUrl  = $baseUrl . $defaultSlug;
        $hreflang[]  = [
            'lang' => 'x-default',
            'href' => htmlspecialchars($defaultUrl, ENT_XML1, 'UTF-8'),
        ];

        // Add each locale variant
        foreach ($supportedLocales as $localeKey => $locale) {
            // For home page, just use locale prefix; for others, add slug after locale
            if ($slug === '') {
                $localeUrl = $baseUrl . '/' . $localeKey;
            } else {
                $localeUrl = $baseUrl . '/' . $localeKey . '/' . $slug;
            }
            $hreflang[] = [
                'lang' => htmlspecialchars($localeKey, ENT_XML1, 'UTF-8'),
                'href' => htmlspecialchars($localeUrl, ENT_XML1, 'UTF-8'),
            ];
        }

        return $hreflang;
    }

    /**
     * Get page slug for sitemap
     */
    public function getPageSlug(Page $page): string
    {
        return normalize_slug($page->slug ?? '', false);
    }

    /**
     * Get last modification date (updated_at or published_at)
     */
    public function getLastModDate(Page $page): Carbon
    {
        return $page->updated_at?->greaterThan($page->published_at ?? now())
            ? $page->updated_at
            : ($page->published_at ?? now());
    }

    /**
     * Calculate priority based on page hierarchy
     */
    public function calculatePriority(Page $page): float
    {
        // Home page or root pages get highest priority
        if (empty($page->parent_id) && ($page->slug === '' || $page->slug === '/')) {
            return 1.0;
        }

        // Root level pages
        if (empty($page->parent_id)) {
            return 0.9;
        }

        // Nested pages get lower priority based on depth
        return 0.7;
    }

    /**
     * Determine change frequency based on page update patterns
     */
    public function getChangeFreq(Page $page): string
    {
        $daysSinceUpdate = now()->diffInDays($page->updated_at ?? $page->published_at ?? now(), true);

        if ($daysSinceUpdate < 1) {
            return 'daily';
        }

        if ($daysSinceUpdate < 7) {
            return 'weekly';
        }

        if ($daysSinceUpdate < 30) {
            return 'monthly';
        }

        return 'yearly';
    }

    /**
     * Get article slug for sitemap
     */
    public function getArticleSlug(Article $article): string
    {
        return normalize_slug($article->slug ?? '', false);
    }

    /**
     * Get article last modification date
     */
    public function getArticleLastModDate(Article $article): Carbon
    {
        return $article->updated_at?->greaterThan($article->published_at ?? now())
            ? $article->updated_at
            : ($article->published_at ?? now());
    }

    /**
     * Determine change frequency for articles
     */
    public function getArticleChangeFreq(Article $article): string
    {
        $daysSinceUpdate = now()->diffInDays($article->updated_at ?? $article->published_at ?? now(), true);

        if ($daysSinceUpdate < 7) {
            return 'weekly';
        }

        if ($daysSinceUpdate < 30) {
            return 'monthly';
        }

        return 'yearly';
    }

    /**
     * Get product slug for sitemap
     */
    public function getProductSlug(Product $product): string
    {
        return normalize_slug($product->slug ?? '', false);
    }

    /**
     * Get product last modification date
     */
    public function getProductLastModDate(Product $product): Carbon
    {
        return $product->updated_at?->greaterThan($product->published_at ?? now())
            ? $product->updated_at
            : ($product->published_at ?? now());
    }

    /**
     * Determine change frequency for products
     */
    public function getProductChangeFreq(Product $product): string
    {
        $daysSinceUpdate = now()->diffInDays($product->updated_at ?? $product->published_at ?? now(), true);

        if ($daysSinceUpdate < 7) {
            return 'weekly';
        }

        if ($daysSinceUpdate < 30) {
            return 'monthly';
        }

        return 'yearly';
    }

    /**
     * Build category path from root to the given category
     * Returns path like: root-slug/category-slug/category-slug
     */
    public function buildCategoryPath($category): string
    {
        if (! $category || ! $category->slug) {
            return '';
        }

        // Get all ancestors from root to this category (including the category itself if method exists)
        $pathParts = [];

        // Try to get ancestors including self, otherwise get ancestors and add self
        if (method_exists($category, 'ancestorsAndSelf')) {
            $categoryChain = $category->ancestorsAndSelf;
        } else {
            $categoryChain = $category->ancestors;
            // Add the category itself at the end
            $categoryChain->push($category);
        }

        // Build path from root to category
        foreach ($categoryChain as $cat) {
            if ($cat->slug) {
                $pathParts[] = normalize_slug($cat->slug, false);
            }
        }

        return implode('/', $pathParts);
    }
}
