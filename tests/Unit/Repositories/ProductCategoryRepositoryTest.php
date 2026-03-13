<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductCategory;
use Appsolutely\AIO\Repositories\ProductCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ProductCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductCategoryRepository::class);
    }

    // --- findBySlug ---

    public function test_find_by_slug_returns_active_category(): void
    {
        $category = ProductCategory::factory()->create([
            'slug'   => 'electronics',
            'status' => Status::ACTIVE,
        ]);

        $result = $this->repository->findBySlug('electronics');

        $this->assertInstanceOf(ProductCategory::class, $result);
        $this->assertEquals($category->id, $result->id);
    }

    public function test_find_by_slug_returns_null_for_inactive_category(): void
    {
        ProductCategory::factory()->create([
            'slug'   => 'inactive-cat',
            'status' => Status::INACTIVE,
        ]);

        $result = $this->repository->findBySlug('inactive-cat');

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $result = $this->repository->findBySlug('non-existent');

        $this->assertNull($result);
    }

    public function test_find_by_slug_is_exact_match(): void
    {
        ProductCategory::factory()->create([
            'slug'   => 'electronics',
            'status' => Status::ACTIVE,
        ]);

        $result = $this->repository->findBySlug('electron');

        $this->assertNull($result);
    }

    // --- getWithProductCount ---

    public function test_get_with_product_count_returns_only_active_categories(): void
    {
        ProductCategory::factory()->create(['status' => Status::ACTIVE]);
        ProductCategory::factory()->create(['status' => Status::ACTIVE]);
        ProductCategory::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getWithProductCount();

        $this->assertCount(2, $result);
    }

    public function test_get_with_product_count_counts_products_correctly(): void
    {
        $category = ProductCategory::factory()->create(['status' => Status::ACTIVE]);
        $product1 = Product::factory()->create(['status' => Status::ACTIVE]);
        $product2 = Product::factory()->create(['status' => Status::ACTIVE]);
        $product1->categories()->attach($category->id);
        $product2->categories()->attach($category->id);

        $result = $this->repository->getWithProductCount();

        $this->assertEquals(2, $result->first()->products_count);
    }

    public function test_get_with_product_count_returns_zero_for_empty_category(): void
    {
        ProductCategory::factory()->create(['status' => Status::ACTIVE]);

        $result = $this->repository->getWithProductCount();

        $this->assertEquals(0, $result->first()->products_count);
    }

    public function test_get_with_product_count_excludes_inactive_categories(): void
    {
        ProductCategory::factory()->create(['status' => Status::ACTIVE]);
        ProductCategory::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getWithProductCount();

        $this->assertCount(1, $result);
    }
}
