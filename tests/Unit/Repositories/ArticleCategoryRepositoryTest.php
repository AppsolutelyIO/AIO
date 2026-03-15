<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\ArticleCategory;
use Appsolutely\AIO\Repositories\ArticleCategoryRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ArticleCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ArticleCategoryRepository::class);
    }

    // --- findBySlug ---

    public function test_find_by_slug_returns_active_category(): void
    {
        $category = ArticleCategory::factory()->create([
            'slug'   => 'tech-news',
            'status' => Status::ACTIVE,
        ]);

        $result = $this->repository->findBySlug('tech-news');

        $this->assertInstanceOf(ArticleCategory::class, $result);
        $this->assertEquals($category->id, $result->id);
    }

    public function test_find_by_slug_returns_null_for_inactive_category(): void
    {
        ArticleCategory::factory()->create([
            'slug'   => 'inactive-cat',
            'status' => Status::INACTIVE,
        ]);

        $result = $this->repository->findBySlug('inactive-cat');

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_null_for_non_existent_slug(): void
    {
        $result = $this->repository->findBySlug('does-not-exist');

        $this->assertNull($result);
    }

    public function test_find_by_slug_is_exact_match(): void
    {
        ArticleCategory::factory()->create([
            'slug'   => 'technology',
            'status' => Status::ACTIVE,
        ]);

        $result = $this->repository->findBySlug('tech');

        $this->assertNull($result);
    }

    // --- getWithArticleCount ---

    public function test_get_with_article_count_returns_active_categories(): void
    {
        ArticleCategory::factory()->create(['status' => Status::ACTIVE]);
        ArticleCategory::factory()->create(['status' => Status::ACTIVE]);
        ArticleCategory::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getWithArticleCount();

        $this->assertCount(2, $result);
    }

    public function test_get_with_article_count_includes_article_count(): void
    {
        $category = ArticleCategory::factory()->create(['status' => Status::ACTIVE]);
        $article1 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article2 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article1->categories()->attach($category->id);
        $article2->categories()->attach($category->id);

        $result = $this->repository->getWithArticleCount();

        $this->assertEquals(2, $result->first()->articles_count);
    }

    public function test_get_with_article_count_returns_zero_count_for_empty_category(): void
    {
        ArticleCategory::factory()->create(['status' => Status::ACTIVE]);

        $result = $this->repository->getWithArticleCount();

        $this->assertEquals(0, $result->first()->articles_count);
    }

    public function test_get_with_article_count_excludes_inactive_categories(): void
    {
        ArticleCategory::factory()->create(['status' => Status::ACTIVE]);
        ArticleCategory::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getWithArticleCount();

        $this->assertCount(1, $result);
    }
}
