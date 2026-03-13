<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Repositories\OrderPaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class OrderPaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderPaymentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(OrderPaymentRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(OrderPaymentRepository::class, $this->repository);
    }

    public function test_model_returns_order_payment_class(): void
    {
        $this->assertEquals(OrderPayment::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_order_payment(): void
    {
        $order   = Order::factory()->create();
        $payment = Payment::create([
            'reference' => 'PAY-OP-001',
            'title'     => 'Test',
            'display'   => 'Test',
            'vendor'    => 'test',
            'handler'   => 'test',
            'status'    => Status::ACTIVE->value,
        ]);

        $orderPayment = $this->repository->create([
            'reference'      => 'ORD-PAY-001',
            'order_id'       => $order->id,
            'payment_id'     => $payment->id,
            'vendor'         => 'test',
            'payment_amount' => 10000,
            'status'         => 'pending',
        ]);

        $this->assertInstanceOf(OrderPayment::class, $orderPayment);
        $this->assertDatabaseHas('order_payments', ['reference' => 'ORD-PAY-001']);
    }

    public function test_find_by_field_returns_payments_for_order(): void
    {
        $order   = Order::factory()->create();
        $payment = Payment::create([
            'reference' => 'PAY-OP-FIND',
            'title'     => 'Test',
            'display'   => 'Test',
            'vendor'    => 'test',
            'handler'   => 'test',
            'status'    => Status::ACTIVE->value,
        ]);

        OrderPayment::create([
            'reference'      => 'ORD-PAY-FIND',
            'order_id'       => $order->id,
            'payment_id'     => $payment->id,
            'vendor'         => 'test',
            'payment_amount' => 5000,
            'status'         => 'pending',
        ]);

        $result = $this->repository->findByField('order_id', $order->id);

        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result->first()->order_id);
    }
}
