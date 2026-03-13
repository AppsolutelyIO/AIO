<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class ProductSkuTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attrs = []): Product
    {
        return Product::factory()->create($attrs);
    }

    private function createSku(Product $product, array $attrs = []): ProductSku
    {
        $id = DB::table('product_skus')->insertGetId(array_merge([
            'product_id' => $product->id,
            'slug'       => 'sku-' . uniqid(),
            'title'      => '',
            'price'      => 1000,
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], $attrs));

        return ProductSku::with('product')->find($id);
    }

    // --- getTitle ---

    public function test_get_title_returns_sku_title_when_set(): void
    {
        $product = $this->createProduct(['title' => 'Product Title']);
        $sku     = $this->createSku($product, ['title' => 'SKU Title']);

        $this->assertEquals('SKU Title', $sku->getTitle());
    }

    public function test_get_title_falls_back_to_product_title_when_empty(): void
    {
        $product = $this->createProduct(['title' => 'Product Title']);
        $sku     = $this->createSku($product, ['title' => '']);

        $this->assertEquals('Product Title', $sku->getTitle());
    }

    // --- getSubtitle ---

    public function test_get_subtitle_returns_sku_subtitle_when_set(): void
    {
        $product = $this->createProduct();
        $sku     = $this->createSku($product, ['subtitle' => 'SKU Subtitle']);

        $this->assertEquals('SKU Subtitle', $sku->getSubtitle());
    }

    public function test_get_subtitle_falls_back_to_product(): void
    {
        $product = $this->createProduct(['subtitle' => 'Product Subtitle']);
        $sku     = $this->createSku($product, ['subtitle' => null]);

        $this->assertEquals('Product Subtitle', $sku->getSubtitle());
    }

    // --- getCover ---

    public function test_get_cover_returns_sku_cover_when_set(): void
    {
        $product = $this->createProduct();
        $sku     = $this->createSku($product, ['cover' => 'sku-cover.jpg']);

        $this->assertEquals('sku-cover.jpg', $sku->getCover());
    }

    public function test_get_cover_falls_back_to_product(): void
    {
        $product = $this->createProduct(['cover' => 'product-cover.jpg']);
        $sku     = $this->createSku($product, ['cover' => null]);

        $this->assertEquals('product-cover.jpg', $sku->getCover());
    }

    public function test_get_cover_returns_null_when_neither_has_cover(): void
    {
        $product = $this->createProduct(['cover' => null]);
        $sku     = $this->createSku($product, ['cover' => null]);

        $this->assertNull($sku->getCover());
    }

    // --- null product safety ---

    public function test_get_title_returns_empty_string_when_product_is_null(): void
    {
        $product = $this->createProduct(['title' => 'Product Title']);
        $sku     = $this->createSku($product, ['title' => '']);

        // Simulate orphaned SKU by setting product relationship to null
        $sku->setRelation('product', null);

        $this->assertSame('', $sku->getTitle());
    }

    public function test_get_subtitle_returns_empty_string_when_product_is_null(): void
    {
        $product = $this->createProduct(['subtitle' => 'Product Subtitle']);
        $sku     = $this->createSku($product, ['subtitle' => null]);

        $sku->setRelation('product', null);

        $this->assertSame('', $sku->getSubtitle());
    }

    public function test_get_cover_returns_null_when_product_is_null(): void
    {
        $product = $this->createProduct(['cover' => 'cover.jpg']);
        $sku     = $this->createSku($product, ['cover' => null]);

        $sku->setRelation('product', null);

        $this->assertNull($sku->getCover());
    }

    public function test_get_keywords_returns_null_when_product_is_null(): void
    {
        $product = $this->createProduct(['keywords' => 'test']);
        $sku     = $this->createSku($product, ['keywords' => null]);

        $sku->setRelation('product', null);

        $this->assertNull($sku->getKeywords());
    }

    public function test_get_original_price_returns_null_when_product_is_null(): void
    {
        $product = $this->createProduct();
        $sku     = $this->createSku($product, ['original_price' => null]);

        $sku->setRelation('product', null);

        $this->assertNull($sku->getOriginalPrice());
    }

    // --- getDescription ---

    public function test_get_description_returns_sku_description_when_set(): void
    {
        $product = $this->createProduct();
        $sku     = $this->createSku($product, ['description' => 'SKU description']);

        $this->assertEquals('SKU description', $sku->getDescription());
    }

    public function test_get_description_falls_back_to_product(): void
    {
        $product = $this->createProduct(['description' => 'Product description']);
        $sku     = $this->createSku($product, ['description' => null]);

        $this->assertEquals('Product description', $sku->getDescription());
    }
}
