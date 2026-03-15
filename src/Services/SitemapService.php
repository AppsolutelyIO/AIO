<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Exceptions\InvalidSitemapTypeException;
use Appsolutely\AIO\Repositories\ArticleRepository;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Repositories\ProductRepository;
use Appsolutely\AIO\Services\Contracts\SitemapServiceInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\View;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Orchestrator service for sitemap generation with caching
 *
 * This service coordinates sitemap XML generation by composing:
 *
 * - PageRepository, ArticleRepository, ProductRepository: Data access for published content
 * - SitemapBuilderService: Handles URL entry building, metadata calculation, and hreflang generation
 * - CacheRepository: Provides caching layer for performance (24-hour TTL)
 *
 * Composition pattern:
 * 1. Retrieves published content from repositories
 * 2. Delegates URL entry building to SitemapBuilderService
 * 3. Caches generated XML to reduce database load
 * 4. Clears cache when content is updated (via observers)
 *
 * This separation provides:
 * - Clear separation: Data access vs. URL building vs. caching
 * - Reusability: SitemapBuilderService can be used independently
 * - Performance: Caching reduces computation on each request
 */
final readonly class SitemapService implements SitemapServiceInterface
{
    private const CACHE_TTL = 86400; // 24 hours

    private const CACHE_KEY = 'sitemap:xml';

    private const CACHE_KEY_PAGE = 'sitemap:page:xml';

    private const CACHE_KEY_ARTICLE = 'sitemap:article:xml';

    private const CACHE_KEY_PRODUCT = 'sitemap:product:xml';

    public function __construct(
        private PageRepository $pageRepository,
        private ArticleRepository $articleRepository,
        private ProductRepository $productRepository,
        private CacheRepository $cache,
        private SitemapBuilderService $builderService
    ) {}

    /**
     * Generate XML sitemap index with caching
     * This is the main sitemap.xml that references sub-sitemaps
     */
    public function generateXml(): string
    {
        return $this->cache->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildSitemapIndex();
        });
    }

    /**
     * Generate page sitemap XML with caching
     */
    public function generatePageXml(): string
    {
        return $this->cache->remember(self::CACHE_KEY_PAGE, self::CACHE_TTL, function () {
            return $this->buildPageSitemap();
        });
    }

    /**
     * Generate article sitemap XML with caching
     */
    public function generateArticleXml(): string
    {
        return $this->cache->remember(self::CACHE_KEY_ARTICLE, self::CACHE_TTL, function () {
            return $this->buildArticleSitemap();
        });
    }

    /**
     * Generate product sitemap XML with caching
     */
    public function generateProductXml(): string
    {
        return $this->cache->remember(self::CACHE_KEY_PRODUCT, self::CACHE_TTL, function () {
            return $this->buildProductSitemap();
        });
    }

    /**
     * Generate sitemap XML by type (page, article, or product) with caching
     */
    public function generateTypeXml(string $type): string
    {
        return match (strtolower($type)) {
            'page'    => $this->generatePageXml(),
            'article' => $this->generateArticleXml(),
            'product' => $this->generateProductXml(),
            default   => throw new InvalidSitemapTypeException($type, ['page', 'article', 'product']),
        };
    }

    /**
     * Clear all sitemap cache (call after updates)
     */
    public function clearCache(): void
    {
        $this->cache->forget(self::CACHE_KEY);
        $this->cache->forget(self::CACHE_KEY_PAGE);
        $this->cache->forget(self::CACHE_KEY_ARTICLE);
        $this->cache->forget(self::CACHE_KEY_PRODUCT);
    }

    /**
     * Build the sitemap index XML that references sub-sitemaps
     * Only includes sitemaps that have content
     */
    private function buildSitemapIndex(): string
    {
        $baseUrl  = rtrim(app_url(), '/');
        $sitemaps = [];

        // Only add page sitemap if there are published pages (home page is always included)
        $pages = $this->getPublishedPages();
        if ($pages->isNotEmpty()) {
            $sitemaps[] = [
                'loc'     => $baseUrl . '/sitemap-page.xml',
                'lastmod' => now()->toAtomString(),
            ];
        }

        // Only add article sitemap if there are published articles
        $articles = $this->getPublishedArticles();
        if ($articles->isNotEmpty()) {
            $sitemaps[] = [
                'loc'     => $baseUrl . '/sitemap-article.xml',
                'lastmod' => now()->toAtomString(),
            ];
        }

        // Only add product sitemap if there are published products
        $products = $this->getPublishedProducts();
        if ($products->isNotEmpty()) {
            $sitemaps[] = [
                'loc'     => $baseUrl . '/sitemap-product.xml',
                'lastmod' => now()->toAtomString(),
            ];
        }

        return View::make('sitemap-index', ['sitemaps' => $sitemaps])->render();
    }

    /**
     * Build the page sitemap XML content using Blade template
     */
    private function buildPageSitemap(): string
    {
        $pages            = $this->getPublishedPages();
        $defaultLocale    = LaravelLocalization::getDefaultLocale();
        $supportedLocales = supported_locales();
        $baseUrl          = rtrim(app_url(), '/');

        $urls = [];

        // Add home page
        $urls[] = $this->builderService->prepareUrlEntry('', now(), 'daily', 1.0, $supportedLocales, $defaultLocale, $baseUrl);

        // Add all published pages (skip if already added as home)
        foreach ($pages as $page) {
            $slug = $this->builderService->getPageSlug($page);

            // Skip if this is the home page (already added above)
            if ($slug === '' || $slug === '/') {
                continue;
            }

            $lastMod    = $this->builderService->getLastModDate($page);
            $priority   = $this->builderService->calculatePriority($page);
            $changeFreq = $this->builderService->getChangeFreq($page);

            $urls[] = $this->builderService->prepareUrlEntry($slug, $lastMod, $changeFreq, $priority, $supportedLocales, $defaultLocale, $baseUrl);
        }

        return View::make('sitemap', ['urls' => $urls])->render();
    }

    /**
     * Build the article sitemap XML content using Blade template
     */
    private function buildArticleSitemap(): string
    {
        $articles          = $this->getPublishedArticles();
        $defaultLocale     = LaravelLocalization::getDefaultLocale();
        $supportedLocales  = supported_locales();
        $baseUrl           = rtrim(app_url(), '/');

        $urls = [];

        foreach ($articles as $article) {
            $articleSlug = $this->builderService->getArticleSlug($article);
            $lastMod     = $this->builderService->getArticleLastModDate($article);
            $priority    = 0.8; // Articles get medium-high priority
            $changeFreq  = $this->builderService->getArticleChangeFreq($article);

            // Get all categories for this article
            $categories = $article->categories;

            if ($categories->isEmpty()) {
                // If no categories, use simple path (fallback)
                $urls[] = $this->builderService->prepareUrlEntry('articles/' . $articleSlug, $lastMod, $changeFreq, $priority, $supportedLocales, $defaultLocale, $baseUrl);
            } else {
                // Generate one URL per category path
                foreach ($categories as $category) {
                    $categoryPath = $this->builderService->buildCategoryPath($category);
                    $fullPath     = path_join($categoryPath, $articleSlug);

                    $urls[] = $this->builderService->prepareUrlEntry($fullPath, $lastMod, $changeFreq, $priority, $supportedLocales, $defaultLocale, $baseUrl);
                }
            }
        }

        return View::make('sitemap', ['urls' => $urls])->render();
    }

    /**
     * Build the product sitemap XML content using Blade template
     */
    private function buildProductSitemap(): string
    {
        $products          = $this->getPublishedProducts();
        $defaultLocale     = LaravelLocalization::getDefaultLocale();
        $supportedLocales  = supported_locales();
        $baseUrl           = rtrim(app_url(), '/');

        $urls = [];

        foreach ($products as $product) {
            $productSlug = $this->builderService->getProductSlug($product);
            $lastMod     = $this->builderService->getProductLastModDate($product);
            $priority    = 0.8; // Products get medium-high priority
            $changeFreq  = $this->builderService->getProductChangeFreq($product);

            // Get all categories for this product
            $categories = $product->categories;

            if ($categories->isEmpty()) {
                // If no categories, use simple path (fallback)
                $urls[] = $this->builderService->prepareUrlEntry('products/' . $productSlug, $lastMod, $changeFreq, $priority, $supportedLocales, $defaultLocale, $baseUrl);
            } else {
                // Generate one URL per category path
                foreach ($categories as $category) {
                    $categoryPath = $this->builderService->buildCategoryPath($category);
                    $fullPath     = path_join($categoryPath, $productSlug);

                    $urls[] = $this->builderService->prepareUrlEntry($fullPath, $lastMod, $changeFreq, $priority, $supportedLocales, $defaultLocale, $baseUrl);
                }
            }
        }

        return View::make('sitemap', ['urls' => $urls])->render();
    }

    /**
     * Get all published pages
     */
    private function getPublishedPages(): Collection
    {
        return $this->pageRepository->getPublishedPagesForSitemap(now());
    }

    /**
     * Get all published articles
     */
    private function getPublishedArticles(): Collection
    {
        return $this->articleRepository->getPublishedArticlesForSitemap(now());
    }

    /**
     * Get all published products
     */
    private function getPublishedProducts(): Collection
    {
        return $this->productRepository->getPublishedProductsForSitemap(now());
    }
}
