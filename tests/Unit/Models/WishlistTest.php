<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\User;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\Wishlist;
use Appsolutely\AIO\Models\WishlistItem;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_belongs_to_user(): void
    {
        $wishlist = Wishlist::factory()->create();

        $this->assertInstanceOf(User::class, $wishlist->user);
    }

    public function test_wishlist_has_many_items(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        WishlistItem::query()->create([
            'wishlist_id' => $wishlist->id,
            'product_id'  => $product->id,
        ]);

        $this->assertCount(1, $wishlist->items);
        $this->assertInstanceOf(WishlistItem::class, $wishlist->items->first());
    }

    public function test_wishlist_item_belongs_to_product(): void
    {
        $wishlist = Wishlist::factory()->create();
        $product  = Product::factory()->create();

        $item = WishlistItem::query()->create([
            'wishlist_id' => $wishlist->id,
            'product_id'  => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $item->product);
        $this->assertEquals($product->id, $item->product->id);
    }

    public function test_wishlist_default_name(): void
    {
        $wishlist = Wishlist::factory()->create();

        $this->assertEquals('Default', $wishlist->name);
    }
}
