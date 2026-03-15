<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\User;
use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderStatusHistory;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_history_belongs_to_order(): void
    {
        $order   = Order::factory()->create(['status' => OrderStatus::Pending]);
        $history = OrderStatusHistory::query()->create([
            'order_id'    => $order->id,
            'from_status' => null,
            'to_status'   => OrderStatus::Pending->value,
        ]);

        $this->assertInstanceOf(Order::class, $history->order);
        $this->assertEquals($order->id, $history->order->id);
    }

    public function test_order_status_history_casts_statuses(): void
    {
        $order   = Order::factory()->create(['status' => OrderStatus::Paid]);
        $history = OrderStatusHistory::query()->create([
            'order_id'    => $order->id,
            'from_status' => OrderStatus::Pending->value,
            'to_status'   => OrderStatus::Paid->value,
        ]);

        $history->refresh();
        $this->assertInstanceOf(OrderStatus::class, $history->from_status);
        $this->assertInstanceOf(OrderStatus::class, $history->to_status);
        $this->assertEquals(OrderStatus::Pending, $history->from_status);
        $this->assertEquals(OrderStatus::Paid, $history->to_status);
    }

    public function test_order_status_history_optional_user(): void
    {
        $user    = User::factory()->create();
        $order   = Order::factory()->create(['status' => OrderStatus::Pending]);
        $history = OrderStatusHistory::query()->create([
            'order_id'    => $order->id,
            'from_status' => null,
            'to_status'   => OrderStatus::Pending->value,
            'user_id'     => $user->id,
            'note'        => 'Order created',
        ]);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals('Order created', $history->note);
    }

    public function test_order_has_status_histories(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        // The observer auto-creates the first history entry
        $histories = $order->statusHistories;

        $this->assertGreaterThanOrEqual(1, $histories->count());
    }
}
