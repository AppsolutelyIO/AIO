<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductImage;
use Appsolutely\AIO\Services\ProductImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class ProductImageServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductImageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductImageService::class);
    }

    public function test_add_image(): void
    {
        $product = Product::factory()->create();

        $image = $this->service->addImage($product->id, [
            'path'       => '/images/product1.jpg',
            'alt'        => 'Product 1',
            'sort'       => 1,
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(ProductImage::class, $image);
        $this->assertEquals($product->id, $image->product_id);
        $this->assertEquals('/images/product1.jpg', $image->path);
        $this->assertTrue($image->is_primary);
    }

    public function test_get_images_by_product(): void
    {
        $product = Product::factory()->create();
        ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/a.jpg', 'sort' => 2]);
        ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/b.jpg', 'sort' => 1]);

        $images = $this->service->getImagesByProduct($product->id);

        $this->assertCount(2, $images);
        $this->assertEquals('/img/b.jpg', $images->first()->path); // sort 1 first
    }

    public function test_get_primary_image(): void
    {
        $product = Product::factory()->create();
        ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/a.jpg', 'is_primary' => false]);
        ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/b.jpg', 'is_primary' => true]);

        $primary = $this->service->getPrimaryImage($product->id);

        $this->assertNotNull($primary);
        $this->assertEquals('/img/b.jpg', $primary->path);
    }

    public function test_get_primary_image_returns_null_when_none(): void
    {
        $product = Product::factory()->create();

        $this->assertNull($this->service->getPrimaryImage($product->id));
    }

    public function test_update_image(): void
    {
        $product = Product::factory()->create();
        $image   = ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/old.jpg', 'alt' => 'Old']);

        $updated = $this->service->updateImage($image, ['alt' => 'New description']);

        $this->assertEquals('New description', $updated->alt);
    }

    public function test_delete_image(): void
    {
        $product = Product::factory()->create();
        $image   = ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/delete.jpg']);

        $result = $this->service->deleteImage($image);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_set_primary_image(): void
    {
        $product = Product::factory()->create();
        $image1  = ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/a.jpg', 'is_primary' => true]);
        $image2  = ProductImage::query()->create(['product_id' => $product->id, 'path' => '/img/b.jpg', 'is_primary' => false]);

        $updated = $this->service->setPrimaryImage($image2);

        $this->assertTrue($updated->is_primary);
        $this->assertFalse($image1->fresh()->is_primary);
    }
}
