<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\NestedUrlResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NestedUrlResolverServiceTest extends TestCase
{
    use RefreshDatabase;

    private NestedUrlResolverService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NestedUrlResolverService::class);
    }

    // --- resolveNestedUrl ---

    public function test_resolve_nested_url_returns_null_when_no_parent_page_found(): void
    {
        $result = $this->service->resolveNestedUrl('/nonexistent/child-article');

        $this->assertNull($result);
    }

    public function test_resolve_nested_url_returns_null_for_single_segment(): void
    {
        Page::factory()->create(['slug' => '/products']);

        $result = $this->service->resolveNestedUrl('/products');

        $this->assertNull($result);
    }

    public function test_resolve_nested_url_returns_null_for_empty_slug(): void
    {
        $result = $this->service->resolveNestedUrl('');

        $this->assertNull($result);
    }

    public function test_resolve_nested_url_returns_null_when_no_blocks_configured(): void
    {
        // Ensure no blocks are configured
        config(['appsolutely.blocks' => []]);

        Page::factory()->create(['slug' => '/blog', 'status' => Status::ACTIVE]);

        $result = $this->service->resolveNestedUrl('/blog/some-article');

        $this->assertNull($result);
    }

    // --- findNestedContent ---

    public function test_find_nested_content_returns_null_when_no_blocks_configured(): void
    {
        config(['appsolutely.blocks' => []]);

        $page   = Page::factory()->create();
        $result = $this->service->findNestedContent($page, 'some-article');

        $this->assertNull($result);
    }

    public function test_find_nested_content_returns_null_when_page_has_no_blocks(): void
    {
        config(['appsolutely.blocks' => ['App\\Blocks\\ArticleBlock' => ['App\\Repositories\\ArticleRepository']]]);

        $page   = Page::factory()->create();
        $result = $this->service->findNestedContent($page, 'some-article');

        $this->assertNull($result);
    }
}
