<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\User;
use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class RefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_refund_can_be_created_with_factory(): void
    {
        $user         = User::factory()->create();
        $order        = Order::factory()->create(['user_id' => $user->id]);
        $orderPayment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $refund = Refund::factory()->create([
            'order_id'         => $order->id,
            'order_payment_id' => $orderPayment->id,
            'user_id'          => $user->id,
        ]);

        $this->assertDatabaseHas('refunds', ['id' => $refund->id]);
        $this->assertEquals(RefundStatus::Pending, $refund->status);
    }

    public function test_refund_belongs_to_order(): void
    {
        $user         = User::factory()->create();
        $order        = Order::factory()->create(['user_id' => $user->id]);
        $orderPayment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $refund = Refund::factory()->create([
            'order_id'         => $order->id,
            'order_payment_id' => $orderPayment->id,
            'user_id'          => $user->id,
        ]);

        $this->assertInstanceOf(Order::class, $refund->order);
        $this->assertEquals($order->id, $refund->order->id);
    }

    public function test_refund_belongs_to_order_payment(): void
    {
        $user         = User::factory()->create();
        $order        = Order::factory()->create(['user_id' => $user->id]);
        $orderPayment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $refund = Refund::factory()->create([
            'order_id'         => $order->id,
            'order_payment_id' => $orderPayment->id,
            'user_id'          => $user->id,
        ]);

        $this->assertInstanceOf(OrderPayment::class, $refund->orderPayment);
    }

    public function test_refunded_state_sets_status_and_timestamp(): void
    {
        $refund = Refund::factory()->refunded()->create();

        $this->assertEquals(RefundStatus::Refunded, $refund->status);
        $this->assertNotNull($refund->refunded_at);
    }
}
