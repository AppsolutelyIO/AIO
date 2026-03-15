<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Services\SitemapBuilderService;
use Appsolutely\AIO\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class SitemapBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    private SitemapBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SitemapBuilderService::class);
    }

    // --- getPageSlug ---

    public function test_get_page_slug_returns_slug_without_leading_slash(): void
    {
        $page = Page::factory()->make(['slug' => 'about-us']);

        $result = $this->service->getPageSlug($page);

        $this->assertEquals('about-us', $result);
        $this->assertStringNotContainsString('/', $result);
    }

    public function test_get_page_slug_returns_empty_for_root_page(): void
    {
        $page = Page::factory()->make(['slug' => '/']);

        $result = $this->service->getPageSlug($page);

        $this->assertEquals('', $result);
    }

    // --- calculatePriority ---

    public function test_calculate_priority_returns_highest_for_root_page(): void
    {
        $page = Page::factory()->make(['slug' => '', 'parent_id' => null]);

        $result = $this->service->calculatePriority($page);

        $this->assertEquals(1.0, $result);
    }

    public function test_calculate_priority_returns_09_for_top_level_page(): void
    {
        $page = Page::factory()->make(['slug' => 'about', 'parent_id' => null]);

        $result = $this->service->calculatePriority($page);

        $this->assertEquals(0.9, $result);
    }

    public function test_calculate_priority_returns_07_for_nested_page(): void
    {
        $page = Page::factory()->make(['slug' => 'child', 'parent_id' => 1]);

        $result = $this->service->calculatePriority($page);

        $this->assertEquals(0.7, $result);
    }

    public function test_calculate_priority_returns_float(): void
    {
        $page = Page::factory()->make(['slug' => 'test', 'parent_id' => null]);

        $this->assertIsFloat($this->service->calculatePriority($page));
    }

    // --- getChangeFreq ---

    public function test_get_change_freq_returns_daily_for_recently_updated(): void
    {
        $page = Page::factory()->create();
        \DB::table('pages')->where('id', $page->id)->update(['updated_at' => now()->subHour()]);
        $page = $page->fresh();

        $result = $this->service->getChangeFreq($page);

        $this->assertEquals('daily', $result);
    }

    public function test_get_change_freq_returns_weekly_for_week_old(): void
    {
        $page = Page::factory()->create();
        \DB::table('pages')->where('id', $page->id)->update(['updated_at' => now()->subDays(3)]);
        $page = $page->fresh();

        $result = $this->service->getChangeFreq($page);

        $this->assertEquals('weekly', $result);
    }

    public function test_get_change_freq_returns_monthly_for_month_old(): void
    {
        $page = Page::factory()->create();
        \DB::table('pages')->where('id', $page->id)->update(['updated_at' => now()->subDays(15)]);
        $page = $page->fresh();

        $result = $this->service->getChangeFreq($page);

        $this->assertEquals('monthly', $result);
    }

    public function test_get_change_freq_returns_yearly_for_old_page(): void
    {
        $page = Page::factory()->create();
        \DB::table('pages')->where('id', $page->id)->update(['updated_at' => now()->subDays(60)]);
        $page = $page->fresh();

        $result = $this->service->getChangeFreq($page);

        $this->assertEquals('yearly', $result);
    }

    public function test_get_change_freq_returns_valid_value(): void
    {
        $page  = Page::factory()->create();
        $valid = ['daily', 'weekly', 'monthly', 'yearly'];

        $this->assertContains($this->service->getChangeFreq($page), $valid);
    }

    // --- getArticleSlug ---

    public function test_get_article_slug_returns_slug_without_leading_slash(): void
    {
        $article = Article::factory()->make(['slug' => 'my-article']);

        $result = $this->service->getArticleSlug($article);

        $this->assertEquals('my-article', $result);
    }

    // --- getArticleChangeFreq ---

    public function test_get_article_change_freq_returns_weekly_for_recent(): void
    {
        $article = Article::factory()->create();
        \DB::table('articles')->where('id', $article->id)->update(['updated_at' => now()->subDays(3)]);
        $article = $article->fresh();

        $result = $this->service->getArticleChangeFreq($article);

        $this->assertEquals('weekly', $result);
    }

    public function test_get_article_change_freq_returns_yearly_for_old(): void
    {
        $article = Article::factory()->create();
        \DB::table('articles')->where('id', $article->id)->update(['updated_at' => now()->subDays(60)]);
        $article = $article->fresh();

        $result = $this->service->getArticleChangeFreq($article);

        $this->assertEquals('yearly', $result);
    }

    // --- getProductSlug ---

    public function test_get_product_slug_returns_slug_without_leading_slash(): void
    {
        $product = Product::factory()->make(['slug' => 'my-product']);

        $result = $this->service->getProductSlug($product);

        $this->assertEquals('my-product', $result);
    }

    // --- getLastModDate ---

    public function test_get_last_mod_date_returns_carbon_instance(): void
    {
        $page = Page::factory()->make([
            'updated_at'   => now(),
            'published_at' => now()->subDay(),
        ]);

        $result = $this->service->getLastModDate($page);

        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function test_get_last_mod_date_returns_updated_at_when_newer(): void
    {
        $published = now()->subDays(10);
        $page      = Page::factory()->create(['published_at' => $published]);
        // updated_at will be set to now() by Eloquent on create, which is newer
        $result = $this->service->getLastModDate($page);

        $this->assertInstanceOf(Carbon::class, $result);
        // updated_at (now) > published_at (10 days ago), so result should be updated_at
        $this->assertTrue($result->greaterThanOrEqualTo($published));
    }
}
