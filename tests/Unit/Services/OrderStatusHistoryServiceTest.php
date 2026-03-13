<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderStatusHistory;
use Appsolutely\AIO\Services\OrderStatusHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class OrderStatusHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderStatusHistoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderStatusHistoryService::class);
    }

    public function test_record_status_change(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $history = $this->service->recordStatusChange(
            $order,
            OrderStatus::Pending,
            OrderStatus::Paid,
            null,
            'Payment received',
        );

        $this->assertInstanceOf(OrderStatusHistory::class, $history);
        $this->assertEquals($order->id, $history->order_id);
        $this->assertEquals(OrderStatus::Pending->value, $history->from_status->value);
        $this->assertEquals(OrderStatus::Paid->value, $history->to_status->value);
        $this->assertEquals('Payment received', $history->note);
    }

    public function test_record_status_change_with_null_from_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $history = $this->service->recordStatusChange(
            $order,
            null,
            OrderStatus::Pending,
        );

        $this->assertNull($history->from_status);
        $this->assertEquals(OrderStatus::Pending, $history->to_status);
    }

    public function test_order_observer_creates_history_on_create(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $histories = OrderStatusHistory::query()
            ->where('order_id', $order->id)
            ->get();

        $this->assertCount(1, $histories);
        $this->assertNull($histories->first()->from_status);
        $this->assertEquals(OrderStatus::Pending, $histories->first()->to_status);
    }

    public function test_order_observer_creates_history_on_update(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $order->update(['status' => OrderStatus::Paid]);

        $histories = OrderStatusHistory::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $histories);
        $this->assertEquals(OrderStatus::Pending, $histories->last()->from_status);
        $this->assertEquals(OrderStatus::Paid, $histories->last()->to_status);
    }
}
