<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\Contracts\PaymentServiceInterface;
use Appsolutely\AIO\Services\PaymentService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

final class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PaymentService::class);
    }

    private function createPayment(array $attrs = []): int
    {
        return DB::table('payments')->insertGetId(array_merge([
            'reference'  => 'pay-' . uniqid(),
            'title'      => 'Test Payment',
            'display'    => 'Credit Card',
            'status'     => 1,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], $attrs));
    }

    // --- Container resolution ---

    public function test_resolves_from_container_via_interface(): void
    {
        $service = app(PaymentServiceInterface::class);

        $this->assertInstanceOf(PaymentService::class, $service);
    }

    public function test_implements_payment_service_interface(): void
    {
        $this->assertInstanceOf(PaymentServiceInterface::class, $this->service);
    }

    // --- getPayments ---

    public function test_get_payments_returns_collection(): void
    {
        $result = $this->service->getPayments();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_get_payments_returns_all_payments(): void
    {
        $this->createPayment();
        $this->createPayment();
        $this->createPayment();

        $result = $this->service->getPayments();

        $this->assertCount(3, $result);
    }

    public function test_get_payments_returns_empty_when_none(): void
    {
        $result = $this->service->getPayments();

        $this->assertEmpty($result);
    }

    public function test_get_payments_returns_models_with_expected_attributes(): void
    {
        $this->createPayment([
            'reference' => 'pay-unique-123',
            'title'     => 'Stripe Gateway',
            'display'   => 'Visa ending 4242',
            'status'    => 1,
        ]);

        $result  = $this->service->getPayments();
        $payment = $result->first();

        $this->assertSame('pay-unique-123', $payment->reference);
        $this->assertSame('Stripe Gateway', $payment->title);
        $this->assertSame('Visa ending 4242', $payment->display);
    }

    public function test_get_payments_includes_inactive_payments(): void
    {
        $this->createPayment(['status' => 1]);
        $this->createPayment(['status' => 0]);

        $result = $this->service->getPayments();

        $this->assertCount(2, $result);
    }
}
