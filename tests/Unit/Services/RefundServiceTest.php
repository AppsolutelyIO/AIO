<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Events\RefundProcessed;
use Appsolutely\AIO\Events\RefundRequested;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Services\RefundService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class RefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private RefundService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RefundService::class);
    }

    public function test_request_refund(): void
    {
        Event::fake();
        $order   = Order::factory()->create(['total_amount' => 50000]);
        $payment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $refund = $this->service->requestRefund($order, $payment, 20000, 'Defective product');

        $this->assertInstanceOf(Refund::class, $refund);
        $this->assertEquals($order->id, $refund->order_id);
        $this->assertEquals($payment->id, $refund->order_payment_id);
        $this->assertEquals(RefundStatus::Pending, $refund->status);
        $this->assertEquals('Defective product', $refund->reason);
        $this->assertStringStartsWith('RF-', $refund->reference);
        Event::assertDispatched(RefundRequested::class);
    }

    public function test_request_refund_fails_when_exceeding_amount(): void
    {
        $order   = Order::factory()->create(['total_amount' => 50000]);
        $payment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeds refundable amount');
        $this->service->requestRefund($order, $payment, 99999999, 'Too much');
    }

    public function test_request_refund_fails_for_zero_amount(): void
    {
        $order   = Order::factory()->create(['total_amount' => 50000]);
        $payment = OrderPayment::factory()->create(['order_id' => $order->id]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be positive');
        $this->service->requestRefund($order, $payment, 0, 'Invalid');
    }

    public function test_approve_refund(): void
    {
        Event::fake();
        $refund = Refund::factory()->create();

        $approved = $this->service->approveRefund($refund, 'Looks valid');

        $this->assertEquals(RefundStatus::Approved, $approved->status);
        $this->assertEquals('Looks valid', $approved->admin_note);
        Event::assertDispatched(RefundProcessed::class);
    }

    public function test_approve_refund_fails_if_not_pending(): void
    {
        $refund = Refund::factory()->approved()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->approveRefund($refund);
    }

    public function test_reject_refund(): void
    {
        Event::fake();
        $refund = Refund::factory()->create();

        $rejected = $this->service->rejectRefund($refund, 'Not eligible');

        $this->assertEquals(RefundStatus::Rejected, $rejected->status);
        $this->assertEquals('Not eligible', $rejected->admin_note);
        Event::assertDispatched(RefundProcessed::class);
    }

    public function test_mark_as_refunded(): void
    {
        Event::fake();
        $refund = Refund::factory()->approved()->create();

        $refunded = $this->service->markAsRefunded($refund, 'VENDOR-REF-123');

        $this->assertEquals(RefundStatus::Refunded, $refunded->status);
        $this->assertEquals('VENDOR-REF-123', $refunded->vendor_reference);
        $this->assertNotNull($refunded->refunded_at);
        Event::assertDispatched(RefundProcessed::class);
    }

    public function test_mark_as_refunded_fails_if_not_approved(): void
    {
        $refund = Refund::factory()->create(); // pending

        $this->expectException(\InvalidArgumentException::class);
        $this->service->markAsRefunded($refund);
    }

    public function test_get_refunds_by_order(): void
    {
        $order = Order::factory()->create();
        Refund::factory()->count(2)->create(['order_id' => $order->id]);

        $refunds = $this->service->getRefundsByOrder($order);

        $this->assertCount(2, $refunds);
    }

    public function test_get_refundable_amount(): void
    {
        $order = Order::factory()->create(['total_amount' => 50000]);

        $refundable = $this->service->getRefundableAmount($order);
        $this->assertGreaterThan(0, $refundable);
    }
}
