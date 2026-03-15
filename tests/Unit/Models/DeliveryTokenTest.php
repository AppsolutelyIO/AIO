<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\DeliveryTokenStatus;
use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_token_belongs_to_order(): void
    {
        $token = DeliveryToken::factory()->create();

        $this->assertInstanceOf(Order::class, $token->order);
    }

    public function test_delivery_token_belongs_to_order_item(): void
    {
        $token = DeliveryToken::factory()->create();

        $this->assertInstanceOf(OrderItem::class, $token->orderItem);
    }

    public function test_delivery_token_casts_product_type(): void
    {
        $token = DeliveryToken::factory()->create();

        $this->assertInstanceOf(ProductType::class, $token->product_type);
    }

    public function test_delivery_token_casts_status(): void
    {
        $token = DeliveryToken::factory()->create();

        $this->assertInstanceOf(DeliveryTokenStatus::class, $token->status);
    }

    public function test_is_pending(): void
    {
        $token = DeliveryToken::factory()->create();

        $this->assertTrue($token->isPending());
    }

    public function test_is_not_pending_when_delivered(): void
    {
        $token = DeliveryToken::factory()->delivered()->create();

        $this->assertFalse($token->isPending());
    }

    public function test_is_expired(): void
    {
        $token = DeliveryToken::factory()->create([
            'expires_at' => now()->subHour(),
        ]);

        $this->assertTrue($token->isExpired());
    }

    public function test_is_not_expired_with_future_date(): void
    {
        $token = DeliveryToken::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $this->assertFalse($token->isExpired());
    }

    public function test_is_deliverable(): void
    {
        $token = DeliveryToken::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($token->isDeliverable());
    }

    public function test_is_not_deliverable_when_expired(): void
    {
        $token = DeliveryToken::factory()->create([
            'expires_at' => now()->subHour(),
        ]);

        $this->assertFalse($token->isDeliverable());
    }

    public function test_is_not_deliverable_when_already_delivered(): void
    {
        $token = DeliveryToken::factory()->delivered()->create();

        $this->assertFalse($token->isDeliverable());
    }

    public function test_order_has_many_delivery_tokens(): void
    {
        $order = Order::factory()->create();
        DeliveryToken::factory()->count(2)->create(['order_id' => $order->id]);

        $this->assertCount(2, $order->deliveryTokens);
    }

    public function test_order_item_has_one_delivery_token(): void
    {
        $order     = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        DeliveryToken::factory()->create([
            'order_id'      => $order->id,
            'order_item_id' => $orderItem->id,
        ]);

        $this->assertInstanceOf(DeliveryToken::class, $orderItem->deliveryToken);
    }

    public function test_manual_virtual_factory(): void
    {
        $token = DeliveryToken::factory()->manualVirtual()->create();

        $this->assertEquals(ProductType::ManualVirtual, $token->product_type);
    }
}
