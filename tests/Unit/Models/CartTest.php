<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\User;
use Appsolutely\AIO\Enums\CartStatus;
use Appsolutely\AIO\Models\Cart;
use Appsolutely\AIO\Models\CartItem;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_can_be_created_with_factory(): void
    {
        $cart = Cart::factory()->create();

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
        $this->assertEquals(CartStatus::Active, $cart->status);
    }

    public function test_cart_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals($user->id, $cart->user->id);
    }

    public function test_cart_has_many_items(): void
    {
        $cart    = Cart::factory()->create();
        $product = Product::factory()->create();

        CartItem::create([
            'cart_id'     => $cart->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'unit_price'  => 1000,
            'total_price' => 2000,
        ]);

        $this->assertCount(1, $cart->items);
        $this->assertInstanceOf(CartItem::class, $cart->items->first());
    }

    public function test_guest_cart_has_session_id(): void
    {
        $cart = Cart::factory()->guest()->create();

        $this->assertNull($cart->user_id);
        $this->assertNotNull($cart->session_id);
    }

    public function test_converted_cart_has_status_and_timestamp(): void
    {
        $cart = Cart::factory()->converted()->create();

        $this->assertEquals(CartStatus::Converted, $cart->status);
        $this->assertNotNull($cart->converted_at);
    }
}
