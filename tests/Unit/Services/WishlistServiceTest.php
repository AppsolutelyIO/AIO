<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use App\Models\User;
use Appsolutely\AIO\Models\Wishlist;
use Appsolutely\AIO\Services\WishlistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class WishlistServiceTest extends TestCase
{
    use RefreshDatabase;

    private WishlistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WishlistService::class);
    }

    public function test_get_or_create_default_creates_wishlist(): void
    {
        $user = User::factory()->create();

        $wishlist = $this->service->getOrCreateDefault($user->id);

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertEquals($user->id, $wishlist->user_id);
        $this->assertEquals('Default', $wishlist->name);
    }

    public function test_get_or_create_default_returns_same_wishlist(): void
    {
        $user = User::factory()->create();

        $wishlist1 = $this->service->getOrCreateDefault($user->id);
        $wishlist2 = $this->service->getOrCreateDefault($user->id);

        $this->assertEquals($wishlist1->id, $wishlist2->id);
    }

    public function test_add_item_to_wishlist(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        $item = $this->service->addItem($wishlist, $product->id);

        $this->assertEquals($product->id, $item->product_id);
        $this->assertNull($item->product_sku_id);
    }

    public function test_add_item_with_sku_to_wishlist(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();
        $sku      = ProductSku::query()->create([
            'product_id' => $product->id,
            'title'      => 'Test SKU',
            'slug'       => 'test-sku',
            'stock'      => 10,
            'price'      => 1000,
            'content'    => '',
        ]);

        $item = $this->service->addItem($wishlist, $product->id, $sku->id);

        $this->assertEquals($sku->id, $item->product_sku_id);
    }

    public function test_add_duplicate_item_returns_existing(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        $item1 = $this->service->addItem($wishlist, $product->id);
        $item2 = $this->service->addItem($wishlist, $product->id);

        $this->assertEquals($item1->id, $item2->id);
    }

    public function test_remove_item_from_wishlist(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        $this->service->addItem($wishlist, $product->id);
        $result = $this->service->removeItem($wishlist, $product->id);

        $this->assertTrue($result);
        $this->assertCount(0, $wishlist->fresh()->items);
    }

    public function test_remove_non_existent_item_returns_false(): void
    {
        $wishlist = Wishlist::factory()->create();

        $result = $this->service->removeItem($wishlist, 999);

        $this->assertFalse($result);
    }

    public function test_has_item_returns_true(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        $this->service->addItem($wishlist, $product->id);

        $this->assertTrue($this->service->hasItem($wishlist, $product->id));
    }

    public function test_has_item_returns_false(): void
    {
        $wishlist = Wishlist::factory()->create();

        $this->assertFalse($this->service->hasItem($wishlist, 999));
    }
}
