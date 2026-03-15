<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use App\Models\User;
use Appsolutely\AIO\Enums\CartStatus;
use Appsolutely\AIO\Models\Cart;
use Appsolutely\AIO\Models\CartItem;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Services\CartService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CartService::class);
    }

    public function test_get_active_cart_creates_new_cart_for_user(): void
    {
        $user = User::factory()->create();

        $cart = $this->service->getActiveCart(userId: $user->id);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals(CartStatus::Active, $cart->status);
    }

    public function test_get_active_cart_returns_existing_cart(): void
    {
        $user         = User::factory()->create();
        $existingCart = Cart::factory()->create(['user_id' => $user->id]);

        $cart = $this->service->getActiveCart(userId: $user->id);

        $this->assertEquals($existingCart->id, $cart->id);
    }

    public function test_get_active_cart_creates_guest_cart_with_session_id(): void
    {
        $sessionId = 'test-session-123';

        $cart = $this->service->getActiveCart(sessionId: $sessionId);

        $this->assertNull($cart->user_id);
        $this->assertEquals($sessionId, $cart->session_id);
    }

    public function test_merge_carts_moves_items_from_guest_to_user_cart(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $userCart  = Cart::factory()->create(['user_id' => $user->id]);
        $guestCart = Cart::factory()->guest()->create();

        CartItem::create([
            'cart_id'     => $guestCart->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'unit_price'  => 1000,
            'total_price' => 2000,
        ]);

        $mergedCart = $this->service->mergeCarts($guestCart, $userCart);

        $this->assertEquals($userCart->id, $mergedCart->id);
        $this->assertCount(1, $mergedCart->items);
        $this->assertEquals($product->id, $mergedCart->items->first()->product_id);

        $guestCart->refresh();
        $this->assertEquals(CartStatus::Abandoned, $guestCart->status);
    }

    public function test_merge_carts_combines_quantities_for_same_product(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $userCart  = Cart::factory()->create(['user_id' => $user->id]);
        $guestCart = Cart::factory()->guest()->create();

        CartItem::create([
            'cart_id'     => $userCart->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'unit_price'  => 1000,
            'total_price' => 2000,
        ]);

        CartItem::create([
            'cart_id'     => $guestCart->id,
            'product_id'  => $product->id,
            'quantity'    => 3,
            'unit_price'  => 1000,
            'total_price' => 3000,
        ]);

        $mergedCart = $this->service->mergeCarts($guestCart, $userCart);

        $this->assertCount(1, $mergedCart->items);
        $this->assertEquals(5, $mergedCart->items->first()->quantity);
    }
}
