<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Repositories\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class PaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PaymentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PaymentRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(PaymentRepository::class, $this->repository);
    }

    public function test_model_returns_payment_class(): void
    {
        $this->assertEquals(Payment::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_payment(): void
    {
        $payment = $this->repository->create([
            'reference' => 'PAY-001',
            'title'     => 'Credit Card',
            'display'   => 'Credit Card Payment',
            'vendor'    => 'stripe',
            'handler'   => 'stripe',
            'status'    => Status::ACTIVE->value,
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertDatabaseHas('payments', ['reference' => 'PAY-001']);
    }

    public function test_find_returns_payment_when_exists(): void
    {
        $payment = Payment::create([
            'reference' => 'PAY-FIND',
            'title'     => 'Test Payment',
            'display'   => 'Test',
            'vendor'    => 'test',
            'handler'   => 'test',
            'status'    => Status::ACTIVE->value,
        ]);

        $result = $this->repository->find($payment->id);

        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals($payment->id, $result->id);
    }

    public function test_find_by_field_returns_active_payments(): void
    {
        Payment::create([
            'reference' => 'PAY-ACTIVE',
            'title'     => 'Active',
            'display'   => 'Active Payment',
            'vendor'    => 'test',
            'handler'   => 'test',
            'status'    => Status::ACTIVE->value,
        ]);
        Payment::create([
            'reference' => 'PAY-INACTIVE',
            'title'     => 'Inactive',
            'display'   => 'Inactive Payment',
            'vendor'    => 'test',
            'handler'   => 'test',
            'status'    => Status::INACTIVE->value,
        ]);

        $result = $this->repository->findByField('status', Status::ACTIVE->value);

        $this->assertCount(1, $result);
        $this->assertEquals('PAY-ACTIVE', $result->first()->reference);
    }
}
