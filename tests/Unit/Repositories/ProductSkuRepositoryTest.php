<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Repositories\ProductSkuRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

final class ProductSkuRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductSkuRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductSkuRepository::class);
    }

    private function createSku(int $productId, array $attrs = []): ProductSku
    {
        $id = DB::table('product_skus')->insertGetId(array_merge([
            'product_id' => $productId,
            'slug'       => 'sku-' . uniqid(),
            'title'      => 'Test SKU',
            'price'      => 1000,
            'sort'       => 0,
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], $attrs));

        return ProductSku::find($id);
    }

    // --- getActiveSkusByProduct ---

    public function test_get_active_skus_by_product_returns_active_skus_for_product(): void
    {
        $product = Product::factory()->create();
        $this->createSku($product->id);
        $this->createSku($product->id);

        $result = $this->repository->getActiveSkusByProduct($product->id);

        $this->assertCount(2, $result);
        $result->each(fn ($s) => $this->assertEquals($product->id, $s->product_id));
    }

    public function test_get_active_skus_by_product_excludes_inactive(): void
    {
        $product = Product::factory()->create();
        $this->createSku($product->id, ['status' => Status::ACTIVE->value]);
        $this->createSku($product->id, ['status' => Status::INACTIVE->value]);

        $result = $this->repository->getActiveSkusByProduct($product->id);

        $this->assertCount(1, $result);
    }

    public function test_get_active_skus_by_product_excludes_other_products(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $this->createSku($product1->id);
        $this->createSku($product2->id);

        $result = $this->repository->getActiveSkusByProduct($product1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($product1->id, $result->first()->product_id);
    }

    public function test_get_active_skus_by_product_returns_ordered_by_sort(): void
    {
        $product = Product::factory()->create();
        $this->createSku($product->id, ['sort' => 3]);
        $this->createSku($product->id, ['sort' => 1]);
        $this->createSku($product->id, ['sort' => 2]);

        $result = $this->repository->getActiveSkusByProduct($product->id);

        $this->assertEquals(1, $result->get(0)->sort);
        $this->assertEquals(2, $result->get(1)->sort);
        $this->assertEquals(3, $result->get(2)->sort);
    }

    public function test_get_active_skus_by_product_returns_empty_when_none(): void
    {
        $product = Product::factory()->create();

        $result = $this->repository->getActiveSkusByProduct($product->id);

        $this->assertCount(0, $result);
    }
}
