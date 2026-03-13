<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Exceptions\InvalidSitemapTypeException;
use Appsolutely\AIO\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Appsolutely\AIO\Tests\TestCase;

final class SitemapServiceTest extends TestCase
{
    use RefreshDatabase;

    private SitemapService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SitemapService::class);
    }

    // --- generateTypeXml ---

    public function test_generate_type_xml_throws_for_invalid_type(): void
    {
        $this->expectException(InvalidSitemapTypeException::class);

        $this->service->generateTypeXml('invalid-type');
    }

    public function test_generate_type_xml_throws_for_unknown_type(): void
    {
        $this->expectException(InvalidSitemapTypeException::class);

        $this->service->generateTypeXml('pdf');
    }

    // --- clearCache ---

    public function test_clear_cache_removes_all_sitemap_cache_keys(): void
    {
        Cache::put('sitemap:xml', 'cached-sitemap', 3600);
        Cache::put('sitemap:page:xml', 'cached-page', 3600);
        Cache::put('sitemap:article:xml', 'cached-article', 3600);
        Cache::put('sitemap:product:xml', 'cached-product', 3600);

        $this->service->clearCache();

        $this->assertNull(Cache::get('sitemap:xml'));
        $this->assertNull(Cache::get('sitemap:page:xml'));
        $this->assertNull(Cache::get('sitemap:article:xml'));
        $this->assertNull(Cache::get('sitemap:product:xml'));
    }

    public function test_clear_cache_is_idempotent_on_empty_cache(): void
    {
        // Clear twice should not throw
        $this->service->clearCache();
        $this->service->clearCache();

        // All keys should be null
        $this->assertNull(Cache::get('sitemap:xml'));
        $this->assertNull(Cache::get('sitemap:page:xml'));
        $this->assertNull(Cache::get('sitemap:article:xml'));
        $this->assertNull(Cache::get('sitemap:product:xml'));
    }
}
