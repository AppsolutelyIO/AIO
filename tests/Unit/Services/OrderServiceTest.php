<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Events\OrderCancelled;
use Appsolutely\AIO\Events\OrderCompleted;
use Appsolutely\AIO\Events\OrderPaid;
use Appsolutely\AIO\Events\OrderShipped;
use Appsolutely\AIO\Events\OrderStatusUpdated;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Appsolutely\AIO\Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
    }

    public function test_find_by_id(): void
    {
        $order = Order::factory()->create();

        $found = $this->service->findById($order->id);

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_missing(): void
    {
        $this->assertNull($this->service->findById(99999));
    }

    public function test_find_by_reference(): void
    {
        $order = Order::factory()->create();

        $found = $this->service->findByReference($order->reference);

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_get_orders_by_user(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create(['user_id' => $order1->user_id]);
        Order::factory()->create(); // different user

        $orders = $this->service->getOrdersByUser($order1->user_id);

        $this->assertCount(2, $orders);
    }

    public function test_can_transition_pending_to_paid(): void
    {
        $order = Order::factory()->create();

        $this->assertTrue($this->service->canTransitionTo($order, OrderStatus::Paid));
    }

    public function test_cannot_transition_completed_to_paid(): void
    {
        $order = Order::factory()->completed()->create();

        $this->assertFalse($this->service->canTransitionTo($order, OrderStatus::Paid));
    }

    public function test_cannot_transition_cancelled_to_anything(): void
    {
        $order = Order::factory()->cancelled()->create();

        $this->assertFalse($this->service->canTransitionTo($order, OrderStatus::Paid));
        $this->assertFalse($this->service->canTransitionTo($order, OrderStatus::Shipped));
    }

    public function test_mark_as_paid(): void
    {
        Event::fake();
        $order = Order::factory()->create();

        $updated = $this->service->markAsPaid($order);

        $this->assertEquals(OrderStatus::Paid, $updated->status);
        Event::assertDispatched(OrderStatusUpdated::class);
        Event::assertDispatched(OrderPaid::class);
    }

    public function test_mark_as_shipped(): void
    {
        Event::fake();
        $order = Order::factory()->paid()->create();

        $updated = $this->service->markAsShipped($order);

        $this->assertEquals(OrderStatus::Shipped, $updated->status);
        Event::assertDispatched(OrderShipped::class);
    }

    public function test_mark_as_completed(): void
    {
        Event::fake();
        $order = Order::factory()->shipped()->create();

        $updated = $this->service->markAsCompleted($order);

        $this->assertEquals(OrderStatus::Completed, $updated->status);
        Event::assertDispatched(OrderCompleted::class);
    }

    public function test_cancel_order(): void
    {
        Event::fake();
        $order = Order::factory()->create();

        $cancelled = $this->service->cancelOrder($order, 'Customer requested');

        $this->assertEquals(OrderStatus::Cancelled, $cancelled->status);
        $this->assertEquals('Customer requested', $cancelled->remark);
        Event::assertDispatched(OrderCancelled::class);
    }

    public function test_cancel_order_fails_for_completed(): void
    {
        $order = Order::factory()->completed()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->cancelOrder($order);
    }

    public function test_update_status_fails_for_invalid_transition(): void
    {
        $order = Order::factory()->create(); // pending

        $this->expectException(\InvalidArgumentException::class);
        $this->service->updateStatus($order, OrderStatus::Shipped); // can't skip paid
    }
}
