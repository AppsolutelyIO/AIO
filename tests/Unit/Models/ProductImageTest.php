<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductImage;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_image_can_be_created(): void
    {
        $product = Product::factory()->create();

        $image = ProductImage::create([
            'product_id' => $product->id,
            'path'       => 'images/product-1.jpg',
            'alt'        => 'Product image',
            'sort'       => 0,
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('product_images', ['id' => $image->id]);
        $this->assertTrue($image->is_primary);
    }

    public function test_product_image_belongs_to_product(): void
    {
        $product = Product::factory()->create();

        $image = ProductImage::create([
            'product_id' => $product->id,
            'path'       => 'images/product-1.jpg',
            'sort'       => 0,
        ]);

        $this->assertInstanceOf(Product::class, $image->product);
        $this->assertEquals($product->id, $image->product->id);
    }

    public function test_product_has_many_images_ordered_by_sort(): void
    {
        $product = Product::factory()->create();

        ProductImage::create([
            'product_id' => $product->id,
            'path'       => 'images/second.jpg',
            'sort'       => 2,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'path'       => 'images/first.jpg',
            'sort'       => 1,
        ]);

        $images = $product->images;
        $this->assertCount(2, $images);
        $this->assertEquals('images/first.jpg', $images->first()->path);
        $this->assertEquals('images/second.jpg', $images->last()->path);
    }
}
