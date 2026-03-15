<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Enums\PaymentMethod;
use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Services\Contracts\PaymentServiceInterface;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentSystemTest extends TestCase
{
    use RefreshDatabase;

    private PaymentServiceInterface $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = $this->app->make(PaymentServiceInterface::class);
    }

    public function test_payment_can_be_created_with_provider_and_method(): void
    {
        $payment = Payment::factory()->stripe()->create();

        $this->assertDatabaseHas('payments', [
            'id'             => $payment->id,
            'provider'       => PaymentProvider::Stripe->value,
            'payment_method' => PaymentMethod::CreditCard->value,
            'vendor'         => 'stripe',
        ]);

        $this->assertInstanceOf(PaymentProvider::class, $payment->provider);
        $this->assertInstanceOf(PaymentMethod::class, $payment->payment_method);
        $this->assertEquals(PaymentProvider::Stripe, $payment->provider);
        $this->assertEquals(PaymentMethod::CreditCard, $payment->payment_method);
    }

    public function test_payment_provider_enum_has_correct_labels(): void
    {
        $this->assertEquals('Stripe', PaymentProvider::Stripe->label());
        $this->assertEquals('PayPal', PaymentProvider::Paypal->label());
        $this->assertEquals('Bank Transfer', PaymentProvider::Bank->label());
        $this->assertEquals('Manual / Offline', PaymentProvider::Manual->label());
        $this->assertEquals('Alipay', PaymentProvider::Alipay->label());
        $this->assertEquals('WeChat Pay', PaymentProvider::WechatPay->label());
        $this->assertEquals('Cryptocurrency', PaymentProvider::Crypto->label());
    }

    public function test_payment_method_enum_has_correct_labels(): void
    {
        $this->assertEquals('Credit Card', PaymentMethod::CreditCard->label());
        $this->assertEquals('Debit Card', PaymentMethod::DebitCard->label());
        $this->assertEquals('Bank Transfer', PaymentMethod::BankTransfer->label());
        $this->assertEquals('E-Wallet', PaymentMethod::EWallet->label());
        $this->assertEquals('QR Code', PaymentMethod::QrCode->label());
        $this->assertEquals('Cryptocurrency', PaymentMethod::Crypto->label());
        $this->assertEquals('Cash on Delivery', PaymentMethod::CashOnDelivery->label());
    }

    public function test_payment_provider_enum_to_array(): void
    {
        $array = PaymentProvider::toArray();

        $this->assertArrayHasKey('stripe', $array);
        $this->assertArrayHasKey('paypal', $array);
        $this->assertArrayHasKey('bank', $array);
        $this->assertEquals('Stripe', $array['stripe']);
    }

    public function test_payment_method_enum_to_array(): void
    {
        $array = PaymentMethod::toArray();

        $this->assertArrayHasKey('credit_card', $array);
        $this->assertArrayHasKey('bank_transfer', $array);
        $this->assertEquals('Credit Card', $array['credit_card']);
    }

    public function test_payment_has_fee_configuration(): void
    {
        $payment = Payment::factory()->stripe()->create();

        $this->assertEquals(2.9, (float) $payment->fee_percentage);
        $this->assertEquals(30, $payment->fee_fixed);
    }

    public function test_payment_calculate_fee(): void
    {
        $payment = Payment::factory()->stripe()->create([
            'fee_percentage' => 2.9,
            'fee_fixed'      => 30,
        ]);

        // 10000 cents ($100) * 2.9% = 290 + 30 fixed = 320
        $this->assertEquals(320, $payment->calculateFee(10000));

        // 0 amount = 0 percentage + 30 fixed = 30
        $this->assertEquals(30, $payment->calculateFee(0));
    }

    public function test_payment_amount_allowed_check(): void
    {
        $payment = Payment::factory()->create([
            'min_amount' => 1000,
            'max_amount' => 100000,
        ]);

        $this->assertTrue($payment->isAmountAllowed(5000));
        $this->assertTrue($payment->isAmountAllowed(1000));
        $this->assertTrue($payment->isAmountAllowed(100000));
        $this->assertFalse($payment->isAmountAllowed(999));
        $this->assertFalse($payment->isAmountAllowed(100001));
    }

    public function test_payment_amount_allowed_with_no_limits(): void
    {
        $payment = Payment::factory()->create([
            'min_amount' => 0,
            'max_amount' => 0,
        ]);

        $this->assertTrue($payment->isAmountAllowed(1));
        $this->assertTrue($payment->isAmountAllowed(999999));
    }

    public function test_payment_has_supported_currencies(): void
    {
        $payment = Payment::factory()->create([
            'supported_currencies' => ['USD', 'EUR', 'GBP'],
        ]);

        $this->assertEquals(['USD', 'EUR', 'GBP'], $payment->supported_currencies);
    }

    public function test_payment_has_test_mode_flag(): void
    {
        $payment = Payment::factory()->testMode()->create();
        $this->assertTrue($payment->is_test_mode);

        $livePayment = Payment::factory()->liveMode()->create();
        $this->assertFalse($livePayment->is_test_mode);
    }

    public function test_product_can_have_specific_payment_methods_via_json(): void
    {
        $stripe  = Payment::factory()->stripe()->create();
        $paypal  = Payment::factory()->paypal()->create();
        $product = Product::factory()->create([
            'payment_methods' => [$stripe->id, $paypal->id],
        ]);

        $this->assertEquals([$stripe->id, $paypal->id], $product->payment_methods);
    }

    public function test_product_get_available_payments_returns_assigned_payments(): void
    {
        $stripe = Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);
        $paypal = Payment::factory()->paypal()->create(['status' => Status::ACTIVE]);
        Payment::factory()->bankTransfer()->create(['status' => Status::ACTIVE]);

        $product = Product::factory()->create([
            'payment_methods' => [$stripe->id, $paypal->id],
        ]);

        $available = $this->paymentService->getAvailablePaymentsForProduct($product);

        $this->assertCount(2, $available);
        $this->assertTrue($available->contains('id', $stripe->id));
        $this->assertTrue($available->contains('id', $paypal->id));
    }

    public function test_product_get_available_payments_falls_back_to_all_active(): void
    {
        $product = Product::factory()->create(['payment_methods' => []]);
        Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);
        Payment::factory()->paypal()->create(['status' => Status::ACTIVE]);
        Payment::factory()->bankTransfer()->create(['status' => Status::INACTIVE]);

        $available = $this->paymentService->getAvailablePaymentsForProduct($product);

        $this->assertCount(2, $available);
    }

    public function test_product_get_available_payments_falls_back_when_null(): void
    {
        $product = Product::factory()->create(['payment_methods' => null]);
        Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);

        $available = $this->paymentService->getAvailablePaymentsForProduct($product);

        $this->assertCount(1, $available);
    }

    public function test_product_get_available_payments_excludes_inactive_assigned(): void
    {
        $stripe = Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);
        $paypal = Payment::factory()->paypal()->create(['status' => Status::INACTIVE]);

        $product = Product::factory()->create([
            'payment_methods' => [$stripe->id, $paypal->id],
        ]);

        $available = $this->paymentService->getAvailablePaymentsForProduct($product);

        $this->assertCount(1, $available);
        $this->assertTrue($available->contains('id', $stripe->id));
    }

    public function test_service_get_active_payments(): void
    {
        Payment::factory()->create(['status' => Status::ACTIVE]);
        Payment::factory()->create(['status' => Status::ACTIVE]);
        Payment::factory()->create(['status' => Status::INACTIVE]);

        $active = $this->paymentService->getActivePayments();

        $this->assertCount(2, $active);
    }

    public function test_service_get_payments_by_provider(): void
    {
        Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);
        Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);
        Payment::factory()->paypal()->create(['status' => Status::ACTIVE]);

        $stripePayments = $this->paymentService->getPaymentsByProvider(PaymentProvider::Stripe);

        $this->assertCount(2, $stripePayments);
    }

    public function test_service_get_available_payments_for_product(): void
    {
        $stripe = Payment::factory()->stripe()->create(['status' => Status::ACTIVE]);

        $product = Product::factory()->create([
            'payment_methods' => [$stripe->id],
        ]);

        $available = $this->paymentService->getAvailablePaymentsForProduct($product);

        $this->assertCount(1, $available);
        $this->assertEquals($stripe->id, $available->first()->id);
    }

    public function test_service_create_payment(): void
    {
        $payment = $this->paymentService->createPayment([
            'reference'      => 'TEST-REF-001',
            'title'          => 'Test Stripe',
            'display'        => 'Credit Card',
            'vendor'         => 'stripe',
            'provider'       => PaymentProvider::Stripe,
            'payment_method' => PaymentMethod::CreditCard,
            'currency'       => 'USD',
            'status'         => Status::ACTIVE,
        ]);

        $this->assertDatabaseHas('payments', [
            'id'        => $payment->id,
            'reference' => 'TEST-REF-001',
            'provider'  => PaymentProvider::Stripe->value,
        ]);
    }

    public function test_service_update_payment(): void
    {
        $payment = Payment::factory()->stripe()->create();

        $updated = $this->paymentService->updatePayment($payment, [
            'title'          => 'Updated Stripe',
            'fee_percentage' => 3.5,
        ]);

        $this->assertEquals('Updated Stripe', $updated->title);
        $this->assertEquals(3.5, (float) $updated->fee_percentage);
    }

    public function test_service_find_payment(): void
    {
        $payment = Payment::factory()->create();

        $found = $this->paymentService->findPayment($payment->id);

        $this->assertNotNull($found);
        $this->assertEquals($payment->id, $found->id);
    }

    public function test_service_find_payment_returns_null_for_missing(): void
    {
        $found = $this->paymentService->findPayment(99999);

        $this->assertNull($found);
    }

    public function test_payment_order_payments_relationship(): void
    {
        $payment = Payment::factory()->create();

        $this->assertCount(0, $payment->orderPayments);
    }
}
