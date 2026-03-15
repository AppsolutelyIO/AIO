<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\DeliveryTokenStatus;
use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Services\DeliveryService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private DeliveryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DeliveryService::class);
    }

    public function test_create_delivery_token_for_auto_virtual(): void
    {
        $product   = Product::factory()->create(['type' => ProductType::AutoVirtual]);
        $order     = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
        ]);

        $token = $this->service->createDeliveryToken($order, $orderItem);

        $this->assertInstanceOf(DeliveryToken::class, $token);
        $this->assertEquals(DeliveryTokenStatus::Pending, $token->status);
        $this->assertEquals(ProductType::AutoVirtual, $token->product_type);
        $this->assertEquals(64, strlen($token->token));
        $this->assertNotNull($token->expires_at);
    }

    public function test_create_delivery_token_for_manual_virtual(): void
    {
        $product   = Product::factory()->create(['type' => ProductType::ManualVirtual]);
        $order     = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
        ]);

        $token = $this->service->createDeliveryToken($order, $orderItem);

        $this->assertEquals(ProductType::ManualVirtual, $token->product_type);
    }

    public function test_create_delivery_token_fails_for_physical(): void
    {
        $product   = Product::factory()->create(['type' => ProductType::Physical]);
        $order     = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Physical products do not use delivery tokens');

        $this->service->createDeliveryToken($order, $orderItem);
    }

    public function test_fulfill_by_token(): void
    {
        $deliveryToken = DeliveryToken::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $result = $this->service->fulfillByToken(
            $deliveryToken->token,
            'License key: ABCD-1234-EFGH',
            'email',
            'system',
        );

        $this->assertEquals(DeliveryTokenStatus::Delivered, $result->status);
        $this->assertEquals('License key: ABCD-1234-EFGH', $result->delivery_payload);
        $this->assertEquals('email', $result->delivery_channel);
        $this->assertEquals('system', $result->delivered_by);
        $this->assertNotNull($result->delivered_at);
    }

    public function test_fulfill_by_token_fails_when_already_delivered(): void
    {
        $deliveryToken = DeliveryToken::factory()->delivered()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not deliverable');

        $this->service->fulfillByToken($deliveryToken->token, 'payload');
    }

    public function test_fulfill_by_token_fails_when_expired(): void
    {
        $deliveryToken = DeliveryToken::factory()->create([
            'expires_at' => now()->subHour(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not deliverable');

        $this->service->fulfillByToken($deliveryToken->token, 'payload');
    }

    public function test_find_by_token(): void
    {
        $deliveryToken = DeliveryToken::factory()->create();

        $found = $this->service->findByToken($deliveryToken->token);

        $this->assertNotNull($found);
        $this->assertEquals($deliveryToken->id, $found->id);
    }

    public function test_find_by_token_returns_null_for_invalid(): void
    {
        $this->assertNull($this->service->findByToken('nonexistent'));
    }

    public function test_is_order_fully_delivered_all_delivered(): void
    {
        $order = Order::factory()->create();
        DeliveryToken::factory()->delivered()->count(2)->create(['order_id' => $order->id]);

        $this->assertTrue($this->service->isOrderFullyDelivered($order));
    }

    public function test_is_order_fully_delivered_some_pending(): void
    {
        $order = Order::factory()->create();
        DeliveryToken::factory()->delivered()->create(['order_id' => $order->id]);
        DeliveryToken::factory()->create(['order_id' => $order->id]);

        $this->assertFalse($this->service->isOrderFullyDelivered($order));
    }

    public function test_is_order_fully_delivered_no_tokens(): void
    {
        $order = Order::factory()->create();

        $this->assertFalse($this->service->isOrderFullyDelivered($order));
    }
}
