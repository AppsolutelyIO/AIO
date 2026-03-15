<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductCategory;
use Appsolutely\AIO\Repositories\ProductRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductRepository::class);
    }

    public function test_get_active_products_returns_only_active_products(): void
    {
        $active1 = Product::factory()->create(['status' => Status::ACTIVE, 'sort' => 1]);
        $active2 = Product::factory()->create(['status' => Status::ACTIVE, 'sort' => 2]);
        Product::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getActiveProducts();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $active1->id));
        $this->assertTrue($result->contains('id', $active2->id));
    }

    public function test_get_active_products_orders_by_sort(): void
    {
        $product1 = Product::factory()->create(['status' => Status::ACTIVE, 'sort' => 3]);
        $product2 = Product::factory()->create(['status' => Status::ACTIVE, 'sort' => 1]);
        $product3 = Product::factory()->create(['status' => Status::ACTIVE, 'sort' => 2]);

        $result = $this->repository->getActiveProducts();

        $this->assertEquals($product2->id, $result->first()->id);
        $this->assertEquals($product3->id, $result->get(1)->id);
        $this->assertEquals($product1->id, $result->last()->id);
    }

    public function test_get_active_list_returns_id_title_array(): void
    {
        $product1 = Product::factory()->create(['status' => Status::ACTIVE, 'title' => 'Product A']);
        $product2 = Product::factory()->create(['status' => Status::ACTIVE, 'title' => 'Product B']);
        Product::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getActiveList();

        $this->assertIsArray($result);
        $this->assertEquals('Product A', $result[$product1->id]);
        $this->assertEquals('Product B', $result[$product2->id]);
        $this->assertCount(2, $result);
    }

    public function test_get_published_products_for_sitemap_returns_only_published(): void
    {
        $published = Product::factory()->create([
            'slug'         => 'product-1',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Create unpublished product (should be excluded)
        Product::factory()->create([
            'slug'         => 'product-2',
            'status'       => Status::INACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Products table requires unique slug and doesn't allow null/empty
        // So we test exclusion by status only (unpublished product above)

        $result = $this->repository->getPublishedProductsForSitemap(now());

        $this->assertCount(1, $result);
        $this->assertEquals($published->id, $result->first()->id);
    }

    public function test_find_by_category_slug_returns_products_in_category(): void
    {
        $category = ProductCategory::factory()->create(['slug' => 'electronics']);
        $product1 = Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $product2 = Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $product3 = Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);

        $product1->categories()->attach($category->id);
        $product2->categories()->attach($category->id);

        $result = $this->repository->findByCategorySlug('electronics');

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $product1->id));
        $this->assertTrue($result->contains('id', $product2->id));
        $this->assertFalse($result->contains('id', $product3->id));
    }

    public function test_get_recent_products_returns_limited_published_products(): void
    {
        Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDays(5)]);
        Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDays(2)]);
        Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        Product::factory()->create(['status' => Status::INACTIVE, 'published_at' => now()->subDay()]);

        $result = $this->repository->getRecentProducts(2);

        $this->assertCount(2, $result);
        $this->assertEquals(now()->subDay()->format('Y-m-d'), $result->first()->published_at->format('Y-m-d'));
    }

    public function test_find_by_slug_with_categories_returns_product_with_categories(): void
    {
        $category = ProductCategory::factory()->create();
        $product  = Product::factory()->create([
            'slug'         => 'test-product',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);
        $product->categories()->attach($category->id);

        $result = $this->repository->findBySlugWithCategories('test-product');

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals($product->id, $result->id);
        $this->assertTrue($result->relationLoaded('categories'));
        $this->assertCount(1, $result->categories);
    }

    public function test_get_published_with_categories_returns_published_products_with_categories(): void
    {
        $category = ProductCategory::factory()->create(['status' => Status::ACTIVE]);
        $product  = Product::factory()->create(['status' => Status::ACTIVE, 'published_at' => now()->subDay()]);
        $product->categories()->attach($category->id);

        Product::factory()->create(['status' => Status::INACTIVE, 'published_at' => now()->subDay()]);

        $result = $this->repository->getPublishedWithCategories();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->relationLoaded('categories'));
    }
}
