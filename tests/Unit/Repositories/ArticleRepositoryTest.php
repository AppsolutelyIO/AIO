<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\ArticleCategory;
use Appsolutely\AIO\Models\Tag;
use Appsolutely\AIO\Repositories\ArticleRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ArticleRepository::class);
    }

    public function test_get_published_articles_returns_only_published(): void
    {
        Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        Article::factory()->create(['status' => Status::INACTIVE, 'published_at' => now()->subDay()]);
        Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->addDay()]);

        $result = $this->repository->getPublishedArticles();

        $this->assertCount(1, $result->get());
    }

    public function test_get_published_articles_filters_by_category(): void
    {
        $category = ArticleCategory::factory()->create(['slug' => 'tech']);
        $article1 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article2 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);

        $article1->categories()->attach($category->id);

        $result = $this->repository->getPublishedArticles(['category_filter' => 'tech']);

        $this->assertCount(1, $result->get());
        $this->assertEquals($article1->id, $result->first()->id);
    }

    public function test_get_published_articles_filters_by_tag(): void
    {
        $tag      = Tag::factory()->create(['slug' => 'laravel']);
        $article1 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article2 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);

        $article1->tags()->attach($tag->id);

        $result = $this->repository->getPublishedArticles(['tag_filter' => 'laravel']);

        $this->assertCount(1, $result->get());
        $this->assertEquals($article1->id, $result->first()->id);
    }

    public function test_find_active_by_slug_returns_published_article(): void
    {
        $article = Article::factory()->create([
            'slug'         => 'test-article',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->repository->findActiveBySlug('test-article', now());

        $this->assertInstanceOf(Article::class, $result);
        $this->assertEquals($article->id, $result->id);
    }

    public function test_find_active_by_slug_returns_null_for_unpublished(): void
    {
        Article::factory()->create([
            'slug'         => 'test-article',
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(),
        ]);

        $result = $this->repository->findActiveBySlug('test-article', now());

        $this->assertNull($result);
    }

    public function test_get_published_articles_for_sitemap_returns_only_published(): void
    {
        $published = Article::factory()->create([
            'slug'         => 'article-1',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Create unpublished article (should be excluded)
        Article::factory()->create([
            'slug'         => 'article-2',
            'status'       => Status::INACTIVE, // Unpublished
            'published_at' => now()->subDay(),
        ]);

        // Create article with null slug - use DB to ensure slug is actually null
        \DB::table('articles')->insert([
            'title'        => 'No Slug Article',
            'slug'         => null,
            'content'      => 'Test content',
            'status'       => Status::ACTIVE->value,
            'published_at' => now()->subDay(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $result = $this->repository->getPublishedArticlesForSitemap(now());

        $this->assertCount(1, $result);
        $this->assertEquals($published->id, $result->first()->id);
    }

    public function test_find_by_category_slug_returns_articles_in_category(): void
    {
        $category = ArticleCategory::factory()->create(['slug' => 'tech']);
        $article1 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article2 = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);

        $article1->categories()->attach($category->id);
        $article2->categories()->attach($category->id);

        $result = $this->repository->findByCategorySlug('tech');

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $article1->id));
        $this->assertTrue($result->contains('id', $article2->id));
    }

    public function test_get_recent_articles_returns_limited_published_articles(): void
    {
        Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDays(5)]);
        Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDays(2)]);
        Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);

        $result = $this->repository->getRecentArticles(2);

        $this->assertCount(2, $result);
        $this->assertEquals(now()->subDay()->format('Y-m-d'), $result->first()->published_at->format('Y-m-d'));
    }

    public function test_get_published_with_categories_returns_published_articles_with_categories(): void
    {
        $category = ArticleCategory::factory()->create(['slug' => 'tech', 'status' => Status::ACTIVE]);
        $article  = Article::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $article->categories()->attach($category->id);

        Article::factory()->create(['status' => Status::INACTIVE, 'published_at' => now()->subDay()]);

        $result = $this->repository->getPublishedWithCategories();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('categories'));
    }
}
