<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Enums\OrderPaymentStatus;
use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Services\Contracts\PaymentGatewayInterface;
use Appsolutely\AIO\Services\PaymentGateways\AdyenGateway;
use Appsolutely\AIO\Services\PaymentGateways\AfterpayGateway;
use Appsolutely\AIO\Services\PaymentGateways\AlipayGateway;
use Appsolutely\AIO\Services\PaymentGateways\ApplePayGateway;
use Appsolutely\AIO\Services\PaymentGateways\BankTransferGateway;
use Appsolutely\AIO\Services\PaymentGateways\BtcpayServerGateway;
use Appsolutely\AIO\Services\PaymentGateways\CoinbaseCommerceGateway;
use Appsolutely\AIO\Services\PaymentGateways\CreemGateway;
use Appsolutely\AIO\Services\PaymentGateways\CryptoGateway;
use Appsolutely\AIO\Services\PaymentGateways\GooglePayGateway;
use Appsolutely\AIO\Services\PaymentGateways\KlarnaGateway;
use Appsolutely\AIO\Services\PaymentGateways\LemonSqueezyGateway;
use Appsolutely\AIO\Services\PaymentGateways\ManualGateway;
use Appsolutely\AIO\Services\PaymentGateways\MercadoPagoGateway;
use Appsolutely\AIO\Services\PaymentGateways\MollieGateway;
use Appsolutely\AIO\Services\PaymentGateways\PaddleGateway;
use Appsolutely\AIO\Services\PaymentGateways\PaymentGatewayFactory;
use Appsolutely\AIO\Services\PaymentGateways\PaypalGateway;
use Appsolutely\AIO\Services\PaymentGateways\RazorpayGateway;
use Appsolutely\AIO\Services\PaymentGateways\SquareGateway;
use Appsolutely\AIO\Services\PaymentGateways\StripeGateway;
use Appsolutely\AIO\Services\PaymentGateways\WechatPayGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    // === Factory Tests ===

    public function test_factory_resolves_all_providers(): void
    {
        $expectations = [
            [PaymentProvider::Stripe, StripeGateway::class],
            [PaymentProvider::Paypal, PaypalGateway::class],
            [PaymentProvider::Bank, BankTransferGateway::class],
            [PaymentProvider::Manual, ManualGateway::class],
            [PaymentProvider::Alipay, AlipayGateway::class],
            [PaymentProvider::WechatPay, WechatPayGateway::class],
            [PaymentProvider::Crypto, CryptoGateway::class],
            [PaymentProvider::Creem, CreemGateway::class],
            [PaymentProvider::Paddle, PaddleGateway::class],
            [PaymentProvider::LemonSqueezy, LemonSqueezyGateway::class],
            [PaymentProvider::Klarna, KlarnaGateway::class],
            [PaymentProvider::Afterpay, AfterpayGateway::class],
            [PaymentProvider::Square, SquareGateway::class],
            [PaymentProvider::Adyen, AdyenGateway::class],
            [PaymentProvider::Mollie, MollieGateway::class],
            [PaymentProvider::Razorpay, RazorpayGateway::class],
            [PaymentProvider::MercadoPago, MercadoPagoGateway::class],
            [PaymentProvider::CoinbaseCommerce, CoinbaseCommerceGateway::class],
            [PaymentProvider::BtcpayServer, BtcpayServerGateway::class],
            [PaymentProvider::ApplePay, ApplePayGateway::class],
            [PaymentProvider::GooglePay, GooglePayGateway::class],
        ];

        foreach ($expectations as [$provider, $expectedClass]) {
            $gateway = PaymentGatewayFactory::makeFromProvider($provider);
            $this->assertInstanceOf($expectedClass, $gateway, "Failed for {$provider->value}");
            $this->assertInstanceOf(PaymentGatewayInterface::class, $gateway);
        }
    }

    public function test_factory_make_from_payment_model(): void
    {
        $payment = Payment::factory()->stripe()->create();
        $gateway = PaymentGatewayFactory::make($payment);

        $this->assertInstanceOf(StripeGateway::class, $gateway);
    }

    public function test_factory_get_registered_gateways(): void
    {
        $gateways = PaymentGatewayFactory::getRegisteredGateways();

        $this->assertCount(21, $gateways);
        $this->assertArrayHasKey('stripe', $gateways);
        $this->assertArrayHasKey('creem', $gateways);
    }

    public function test_factory_register_custom_gateway(): void
    {
        PaymentGatewayFactory::register('test_provider', ManualGateway::class);

        $gateways = PaymentGatewayFactory::getRegisteredGateways();
        $this->assertArrayHasKey('test_provider', $gateways);

        // Clean up
        $reflection = new \ReflectionClass(PaymentGatewayFactory::class);
        $prop       = $reflection->getProperty('gateways');
        $current    = $prop->getValue();
        unset($current['test_provider']);
        $prop->setValue(null, $current);
    }

    // === PaymentGatewayResult DTO Tests ===

    public function test_result_success(): void
    {
        $result = PaymentGatewayResult::success('REF-123', null, ['key' => 'value']);

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Paid, $result->status);
        $this->assertEquals('REF-123', $result->vendorReference);
        $this->assertNull($result->redirectUrl);
        $this->assertEquals(['key' => 'value'], $result->vendorExtraInfo);
        $this->assertNull($result->errorMessage);
    }

    public function test_result_pending(): void
    {
        $result = PaymentGatewayResult::pending('REF-456');

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Pending, $result->status);
        $this->assertEquals('REF-456', $result->vendorReference);
    }

    public function test_result_redirect(): void
    {
        $result = PaymentGatewayResult::redirect('https://pay.example.com', 'SESSION-789');

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Pending, $result->status);
        $this->assertEquals('https://pay.example.com', $result->redirectUrl);
        $this->assertEquals('SESSION-789', $result->vendorReference);
    }

    public function test_result_failure(): void
    {
        $result = PaymentGatewayResult::failure('Card declined');

        $this->assertFalse($result->success);
        $this->assertEquals(OrderPaymentStatus::Failed, $result->status);
        $this->assertEquals('Card declined', $result->errorMessage);
    }

    // === Gateway Capability Tests ===

    public function test_stripe_gateway_capabilities(): void
    {
        $gateway = new StripeGateway();

        $this->assertEquals('Stripe', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_paypal_gateway_capabilities(): void
    {
        $gateway = new PaypalGateway();

        $this->assertEquals('PayPal', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_bank_transfer_gateway_capabilities(): void
    {
        $gateway = new BankTransferGateway();

        $this->assertEquals('Bank Transfer', $gateway->getName());
        $this->assertFalse($gateway->supportsRefund());
        $this->assertFalse($gateway->requiresRedirect());
    }

    public function test_manual_gateway_capabilities(): void
    {
        $gateway = new ManualGateway();

        $this->assertEquals('Manual / Offline', $gateway->getName());
        $this->assertFalse($gateway->supportsRefund());
        $this->assertFalse($gateway->requiresRedirect());
    }

    public function test_alipay_gateway_capabilities(): void
    {
        $gateway = new AlipayGateway();

        $this->assertEquals('Alipay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_wechat_pay_gateway_capabilities(): void
    {
        $gateway = new WechatPayGateway();

        $this->assertEquals('WeChat Pay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_crypto_gateway_capabilities(): void
    {
        $gateway = new CryptoGateway();

        $this->assertEquals('Cryptocurrency', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_creem_gateway_capabilities(): void
    {
        $gateway = new CreemGateway();

        $this->assertEquals('Creem', $gateway->getName());
        $this->assertFalse($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    // === Offline Gateway Charge Tests ===

    public function test_bank_transfer_charge_returns_pending(): void
    {
        $order   = Order::factory()->create();
        $payment = Payment::factory()->bankTransfer()->create([
            'instruction' => 'Transfer to ACME Bank, Account 123456',
            'setting'     => ['bank_name' => 'ACME Bank', 'bank_account' => '123456', 'account_name' => 'Test Inc'],
        ]);

        $gateway = new BankTransferGateway();
        $result  = $gateway->charge($order, $payment, 10000);

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Pending, $result->status);
        $this->assertStringStartsWith('BANK-', $result->vendorReference);
        $this->assertEquals('ACME Bank', $result->vendorExtraInfo['bank_name']);
        $this->assertEquals('123456', $result->vendorExtraInfo['bank_account']);
        $this->assertEquals('Test Inc', $result->vendorExtraInfo['account_name']);
    }

    public function test_manual_charge_returns_pending(): void
    {
        $order   = Order::factory()->create();
        $payment = Payment::factory()->create([
            'provider'    => PaymentProvider::Manual,
            'instruction' => 'Pay at counter',
        ]);

        $gateway = new ManualGateway();
        $result  = $gateway->charge($order, $payment, 5000);

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Pending, $result->status);
        $this->assertStringStartsWith('MANUAL-', $result->vendorReference);
        $this->assertEquals('Pay at counter', $result->vendorExtraInfo['instruction']);
    }

    public function test_crypto_charge_returns_pending(): void
    {
        $order   = Order::factory()->create();
        $payment = Payment::factory()->create([
            'provider'    => PaymentProvider::Crypto,
            'instruction' => 'Send to wallet',
            'setting'     => ['wallet_address' => '0xABC123', 'network' => 'Ethereum'],
        ]);

        $gateway = new CryptoGateway();
        $result  = $gateway->charge($order, $payment, 100000);

        $this->assertTrue($result->success);
        $this->assertEquals(OrderPaymentStatus::Pending, $result->status);
        $this->assertEquals('0xABC123', $result->vendorExtraInfo['wallet_address']);
        $this->assertEquals('Ethereum', $result->vendorExtraInfo['network']);
    }

    // === Refund Unsupported Tests ===

    public function test_bank_transfer_refund_returns_failure(): void
    {
        $orderPayment = OrderPayment::factory()->create();

        $gateway = new BankTransferGateway();
        $result  = $gateway->refund($orderPayment, 1000);

        $this->assertFalse($result->success);
        $this->assertEquals(OrderPaymentStatus::Failed, $result->status);
    }

    public function test_creem_refund_returns_failure(): void
    {
        $orderPayment = OrderPayment::factory()->create();

        $gateway = new CreemGateway();
        $result  = $gateway->refund($orderPayment, 1000);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('dashboard', $result->errorMessage);
    }

    public function test_crypto_manual_refund_returns_failure(): void
    {
        $orderPayment = OrderPayment::factory()->create();
        $orderPayment->payment->update([
            'setting' => ['crypto_provider' => 'manual'],
        ]);

        $gateway = new CryptoGateway();
        $result  = $gateway->refund($orderPayment->fresh(), 1000);

        $this->assertFalse($result->success);
        $this->assertStringContainsString('manual crypto', $result->errorMessage);
    }

    // === Alipay Webhook Verification ===

    public function test_alipay_webhook_verification_succeeds(): void
    {
        $gateway = new AlipayGateway();

        // Generate RSA key pair for testing
        $keyPair    = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKey  = $keyDetails['key'];

        // Build webhook data (as Alipay sends it)
        $data = [
            'trade_no'     => '2024031222001400001',
            'out_trade_no' => 'ORD-12345',
            'trade_status' => 'TRADE_SUCCESS',
            'total_amount' => '100.00',
        ];

        ksort($data);
        $signContent = urldecode(http_build_query($data));

        openssl_sign($signContent, $signature, $keyPair, OPENSSL_ALGO_SHA256);
        $data['sign']      = base64_encode($signature);
        $data['sign_type'] = 'RSA2';

        $payload = http_build_query($data);

        $result = $gateway->verifyWebhook($payload, [], $publicKey);

        $this->assertEquals('TRADE_SUCCESS', $result['event']);
        $this->assertEquals('2024031222001400001', $result['vendor_reference']);
        $this->assertEquals('TRADE_SUCCESS', $result['status']);
    }

    public function test_alipay_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new AlipayGateway();

        // Generate a key pair for verification
        $keyPair    = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKey  = $keyDetails['key'];

        $data = [
            'trade_no'     => '123',
            'trade_status' => 'TRADE_SUCCESS',
            'sign'         => base64_encode('invalid_signature'),
            'sign_type'    => 'RSA2',
        ];

        $payload = http_build_query($data);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook($payload, [], $publicKey);
    }

    // === Crypto Webhook Verification ===

    public function test_crypto_webhook_verification_succeeds(): void
    {
        $gateway = new CryptoGateway();
        $secret  = 'test_api_token';
        $payload = json_encode([
            'id'     => 12345,
            'status' => 'paid',
        ]);

        $result = $gateway->verifyWebhook($payload, ['authorization' => "Bearer {$secret}"], $secret);

        $this->assertEquals('paid', $result['event']);
        $this->assertEquals('12345', $result['vendor_reference']);
        $this->assertEquals('paid', $result['status']);
    }

    public function test_crypto_webhook_verification_fails_with_invalid_token(): void
    {
        $gateway = new CryptoGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('verification failed');

        $gateway->verifyWebhook('{"status":"paid"}', ['authorization' => 'Bearer wrong_token'], 'correct_secret');
    }

    // === Creem Webhook Verification ===

    public function test_creem_webhook_verification_succeeds(): void
    {
        $gateway = new CreemGateway();
        $secret  = 'test_webhook_secret';
        $payload = json_encode([
            'eventType' => 'checkout.completed',
            'object'    => [
                'id'     => 'chk_123',
                'status' => 'completed',
            ],
        ]);

        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $gateway->verifyWebhook($payload, ['creem-signature' => $signature], $secret);

        $this->assertEquals('checkout.completed', $result['event']);
        $this->assertEquals('chk_123', $result['vendor_reference']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_creem_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new CreemGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['creem-signature' => 'invalid'], 'secret');
    }

    // === Creem Enum & Factory ===

    public function test_creem_provider_enum(): void
    {
        $this->assertEquals('creem', PaymentProvider::Creem->value);
        $this->assertEquals('Creem', PaymentProvider::Creem->label());

        $array = PaymentProvider::toArray();
        $this->assertArrayHasKey('creem', $array);
        $this->assertEquals('Creem', $array['creem']);
    }

    public function test_creem_factory_state(): void
    {
        $payment = Payment::factory()->creem()->create();

        $this->assertEquals(PaymentProvider::Creem, $payment->provider);
        $this->assertEquals('creem', $payment->vendor);
        $this->assertEquals('Creem', $payment->title);
    }

    // === New Gateway Capability Tests ===

    public function test_paddle_gateway_capabilities(): void
    {
        $gateway = new PaddleGateway();

        $this->assertEquals('Paddle', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_lemon_squeezy_gateway_capabilities(): void
    {
        $gateway = new LemonSqueezyGateway();

        $this->assertEquals('Lemon Squeezy', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_klarna_gateway_capabilities(): void
    {
        $gateway = new KlarnaGateway();

        $this->assertEquals('Klarna', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_afterpay_gateway_capabilities(): void
    {
        $gateway = new AfterpayGateway();

        $this->assertEquals('Afterpay / Clearpay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_square_gateway_capabilities(): void
    {
        $gateway = new SquareGateway();

        $this->assertEquals('Square', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_adyen_gateway_capabilities(): void
    {
        $gateway = new AdyenGateway();

        $this->assertEquals('Adyen', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_mollie_gateway_capabilities(): void
    {
        $gateway = new MollieGateway();

        $this->assertEquals('Mollie', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_razorpay_gateway_capabilities(): void
    {
        $gateway = new RazorpayGateway();

        $this->assertEquals('Razorpay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_mercado_pago_gateway_capabilities(): void
    {
        $gateway = new MercadoPagoGateway();

        $this->assertEquals('MercadoPago', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_coinbase_commerce_gateway_capabilities(): void
    {
        $gateway = new CoinbaseCommerceGateway();

        $this->assertEquals('Coinbase Commerce', $gateway->getName());
        $this->assertFalse($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_btcpay_server_gateway_capabilities(): void
    {
        $gateway = new BtcpayServerGateway();

        $this->assertEquals('BTCPay Server', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_apple_pay_gateway_capabilities(): void
    {
        $gateway = new ApplePayGateway();

        $this->assertEquals('Apple Pay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    public function test_google_pay_gateway_capabilities(): void
    {
        $gateway = new GooglePayGateway();

        $this->assertEquals('Google Pay', $gateway->getName());
        $this->assertTrue($gateway->supportsRefund());
        $this->assertTrue($gateway->requiresRedirect());
    }

    // === New Provider Enum Tests ===

    public function test_new_provider_enums(): void
    {
        $newProviders = [
            ['paddle', 'Paddle', PaymentProvider::Paddle],
            ['lemon_squeezy', 'Lemon Squeezy', PaymentProvider::LemonSqueezy],
            ['klarna', 'Klarna', PaymentProvider::Klarna],
            ['afterpay', 'Afterpay / Clearpay', PaymentProvider::Afterpay],
            ['square', 'Square', PaymentProvider::Square],
            ['adyen', 'Adyen', PaymentProvider::Adyen],
            ['mollie', 'Mollie', PaymentProvider::Mollie],
            ['razorpay', 'Razorpay', PaymentProvider::Razorpay],
            ['mercado_pago', 'MercadoPago', PaymentProvider::MercadoPago],
            ['coinbase_commerce', 'Coinbase Commerce', PaymentProvider::CoinbaseCommerce],
            ['btcpay_server', 'BTCPay Server', PaymentProvider::BtcpayServer],
            ['apple_pay', 'Apple Pay', PaymentProvider::ApplePay],
            ['google_pay', 'Google Pay', PaymentProvider::GooglePay],
        ];

        $array = PaymentProvider::toArray();

        foreach ($newProviders as [$value, $label, $enum]) {
            $this->assertEquals($value, $enum->value);
            $this->assertEquals($label, $enum->label());
            $this->assertArrayHasKey($value, $array);
            $this->assertEquals($label, $array[$value]);
        }
    }

    public function test_buy_now_pay_later_payment_method(): void
    {
        $this->assertEquals('buy_now_pay_later', \Appsolutely\AIO\Enums\PaymentMethod::BuyNowPayLater->value);
        $this->assertEquals('Buy Now, Pay Later', \Appsolutely\AIO\Enums\PaymentMethod::BuyNowPayLater->label());
    }

    // === New Provider Factory State Tests ===

    public function test_paddle_factory_state(): void
    {
        $payment = Payment::factory()->paddle()->create();

        $this->assertEquals(PaymentProvider::Paddle, $payment->provider);
        $this->assertEquals('paddle', $payment->vendor);
        $this->assertEquals('Paddle', $payment->title);
    }

    public function test_lemon_squeezy_factory_state(): void
    {
        $payment = Payment::factory()->lemonSqueezy()->create();

        $this->assertEquals(PaymentProvider::LemonSqueezy, $payment->provider);
        $this->assertEquals('lemon_squeezy', $payment->vendor);
        $this->assertEquals('Lemon Squeezy', $payment->title);
    }

    public function test_klarna_factory_state(): void
    {
        $payment = Payment::factory()->klarna()->create();

        $this->assertEquals(PaymentProvider::Klarna, $payment->provider);
        $this->assertEquals('klarna', $payment->vendor);
        $this->assertEquals('Klarna', $payment->title);
    }

    public function test_afterpay_factory_state(): void
    {
        $payment = Payment::factory()->afterpay()->create();

        $this->assertEquals(PaymentProvider::Afterpay, $payment->provider);
        $this->assertEquals('afterpay', $payment->vendor);
    }

    public function test_square_factory_state(): void
    {
        $payment = Payment::factory()->square()->create();

        $this->assertEquals(PaymentProvider::Square, $payment->provider);
        $this->assertEquals('square', $payment->vendor);
    }

    public function test_adyen_factory_state(): void
    {
        $payment = Payment::factory()->adyen()->create();

        $this->assertEquals(PaymentProvider::Adyen, $payment->provider);
        $this->assertEquals('adyen', $payment->vendor);
    }

    public function test_mollie_factory_state(): void
    {
        $payment = Payment::factory()->mollie()->create();

        $this->assertEquals(PaymentProvider::Mollie, $payment->provider);
        $this->assertEquals('mollie', $payment->vendor);
    }

    public function test_razorpay_factory_state(): void
    {
        $payment = Payment::factory()->razorpay()->create();

        $this->assertEquals(PaymentProvider::Razorpay, $payment->provider);
        $this->assertEquals('razorpay', $payment->vendor);
        $this->assertEquals('INR', $payment->currency);
    }

    public function test_mercado_pago_factory_state(): void
    {
        $payment = Payment::factory()->mercadoPago()->create();

        $this->assertEquals(PaymentProvider::MercadoPago, $payment->provider);
        $this->assertEquals('mercado_pago', $payment->vendor);
    }

    public function test_coinbase_commerce_factory_state(): void
    {
        $payment = Payment::factory()->coinbaseCommerce()->create();

        $this->assertEquals(PaymentProvider::CoinbaseCommerce, $payment->provider);
        $this->assertEquals('coinbase_commerce', $payment->vendor);
    }

    public function test_btcpay_server_factory_state(): void
    {
        $payment = Payment::factory()->btcpayServer()->create();

        $this->assertEquals(PaymentProvider::BtcpayServer, $payment->provider);
        $this->assertEquals('btcpay_server', $payment->vendor);
    }

    public function test_apple_pay_factory_state(): void
    {
        $payment = Payment::factory()->applePay()->create();

        $this->assertEquals(PaymentProvider::ApplePay, $payment->provider);
        $this->assertEquals('apple_pay', $payment->vendor);
    }

    public function test_google_pay_factory_state(): void
    {
        $payment = Payment::factory()->googlePay()->create();

        $this->assertEquals(PaymentProvider::GooglePay, $payment->provider);
        $this->assertEquals('google_pay', $payment->vendor);
    }

    // === New Webhook Verification Tests ===

    public function test_paddle_webhook_verification_succeeds(): void
    {
        $gateway = new PaddleGateway();
        $secret  = 'test_paddle_secret';
        $payload = json_encode([
            'event_type' => 'transaction.completed',
            'data'       => ['id' => 'txn_123', 'status' => 'completed'],
        ]);

        $ts        = time();
        $h1        = hash_hmac('sha256', "{$ts}:{$payload}", $secret);
        $signature = "ts={$ts};h1={$h1}";

        $result = $gateway->verifyWebhook($payload, ['paddle-signature' => $signature], $secret);

        $this->assertEquals('transaction.completed', $result['event']);
        $this->assertEquals('txn_123', $result['vendor_reference']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_paddle_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new PaddleGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['paddle-signature' => 'ts=123;h1=invalid'], 'secret');
    }

    public function test_lemon_squeezy_webhook_verification_succeeds(): void
    {
        $gateway = new LemonSqueezyGateway();
        $secret  = 'test_ls_secret';
        $payload = json_encode([
            'meta' => ['event_name' => 'order_created'],
            'data' => ['id' => '12345', 'attributes' => ['status' => 'paid']],
        ]);

        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $gateway->verifyWebhook($payload, ['x-signature' => $signature], $secret);

        $this->assertEquals('order_created', $result['event']);
        $this->assertEquals('12345', $result['vendor_reference']);
        $this->assertEquals('paid', $result['status']);
    }

    public function test_lemon_squeezy_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new LemonSqueezyGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['x-signature' => 'invalid'], 'secret');
    }

    public function test_afterpay_webhook_verification_succeeds(): void
    {
        $gateway = new AfterpayGateway();
        $secret  = 'test_afterpay_secret';
        $payload = json_encode([
            'type' => 'payment.captured',
            'data' => ['id' => 'ap_123', 'status' => 'approved'],
        ]);

        $signature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        $result = $gateway->verifyWebhook($payload, ['x-afterpay-request-signature' => $signature], $secret);

        $this->assertEquals('payment.captured', $result['event']);
        $this->assertEquals('ap_123', $result['vendor_reference']);
        $this->assertEquals('approved', $result['status']);
    }

    public function test_afterpay_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new AfterpayGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['x-afterpay-request-signature' => 'invalid'], 'secret');
    }

    public function test_razorpay_webhook_verification_succeeds(): void
    {
        $gateway = new RazorpayGateway();
        $secret  = 'test_razorpay_secret';
        $payload = json_encode([
            'event'   => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_123', 'status' => 'captured']]],
        ]);

        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $gateway->verifyWebhook($payload, ['x-razorpay-signature' => $signature], $secret);

        $this->assertEquals('payment.captured', $result['event']);
        $this->assertEquals('pay_123', $result['vendor_reference']);
        $this->assertEquals('captured', $result['status']);
    }

    public function test_razorpay_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new RazorpayGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['x-razorpay-signature' => 'invalid'], 'secret');
    }

    public function test_coinbase_commerce_webhook_verification_succeeds(): void
    {
        $gateway = new CoinbaseCommerceGateway();
        $secret  = 'test_cc_secret';
        $payload = json_encode([
            'event' => [
                'type' => 'charge:confirmed',
                'data' => [
                    'id'       => 'charge_123',
                    'code'     => 'ABCDEF',
                    'timeline' => [['status' => 'COMPLETED']],
                ],
            ],
        ]);

        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $gateway->verifyWebhook($payload, ['x-cc-webhook-signature' => $signature], $secret);

        $this->assertEquals('charge:confirmed', $result['event']);
        $this->assertEquals('ABCDEF', $result['vendor_reference']);
        $this->assertEquals('COMPLETED', $result['status']);
    }

    public function test_coinbase_commerce_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new CoinbaseCommerceGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['x-cc-webhook-signature' => 'invalid'], 'secret');
    }

    public function test_btcpay_server_webhook_verification_succeeds(): void
    {
        $gateway = new BtcpayServerGateway();
        $secret  = 'test_btcpay_secret';
        $payload = json_encode([
            'type'      => 'InvoiceSettled',
            'invoiceId' => 'inv_123',
        ]);

        $signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        $result = $gateway->verifyWebhook($payload, ['btcpay-sig' => $signature], $secret);

        $this->assertEquals('InvoiceSettled', $result['event']);
        $this->assertEquals('inv_123', $result['vendor_reference']);
    }

    public function test_btcpay_server_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new BtcpayServerGateway();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook('{"test": true}', ['btcpay-sig' => 'sha256=invalid'], 'secret');
    }

    public function test_mercado_pago_webhook_verification_succeeds(): void
    {
        $gateway   = new MercadoPagoGateway();
        $secret    = 'test_mp_secret';
        $dataId    = '12345';
        $requestId = 'req-abc';
        $ts        = (string) time();

        $payload = json_encode([
            'type'   => 'payment',
            'action' => 'payment.created',
            'data'   => ['id' => $dataId],
        ]);

        $manifest     = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
        $expectedHash = hash_hmac('sha256', $manifest, $secret);
        $xSignature   = "ts={$ts},v1={$expectedHash}";

        $result = $gateway->verifyWebhook($payload, [
            'x-signature'  => $xSignature,
            'x-request-id' => $requestId,
        ], $secret);

        $this->assertEquals('payment', $result['event']);
        $this->assertEquals($dataId, $result['vendor_reference']);
    }

    public function test_mercado_pago_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new MercadoPagoGateway();

        $payload = json_encode(['data' => ['id' => '123']]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook($payload, [
            'x-signature'  => 'ts=123,v1=invalid',
            'x-request-id' => 'req-abc',
        ], 'secret');
    }

    public function test_adyen_webhook_verification_succeeds(): void
    {
        $gateway = new AdyenGateway();
        $secret  = '44782DEF547AAA06C910C43D1F16564A10667222A1A6D14BDEE26CAF7D28B2E4'; // hex-encoded HMAC key

        $item = [
            'pspReference'        => '7914073381342284',
            'originalReference'   => '',
            'merchantAccountCode' => 'TestMerchant',
            'merchantReference'   => 'TestPayment-1407325143704',
            'amount'              => ['value' => 1130, 'currency' => 'EUR'],
            'eventCode'           => 'AUTHORISATION',
            'success'             => 'true',
            'additionalData'      => [],
        ];

        $signPayload = implode(':', [
            $item['pspReference'],
            $item['originalReference'],
            $item['merchantAccountCode'],
            $item['merchantReference'],
            $item['amount']['value'],
            $item['amount']['currency'],
            $item['eventCode'],
            $item['success'],
        ]);

        $hmac                                    = base64_encode(hash_hmac('sha256', $signPayload, hex2bin($secret), true));
        $item['additionalData']['hmacSignature'] = $hmac;

        $payload = json_encode([
            'notificationItems' => [
                ['NotificationRequestItem' => $item],
            ],
        ]);

        $result = $gateway->verifyWebhook($payload, [], $secret);

        $this->assertEquals('AUTHORISATION', $result['event']);
        $this->assertEquals('7914073381342284', $result['vendor_reference']);
        $this->assertEquals('success', $result['status']);
    }

    public function test_adyen_webhook_verification_fails_with_invalid_signature(): void
    {
        $gateway = new AdyenGateway();

        $payload = json_encode([
            'notificationItems' => [
                ['NotificationRequestItem' => [
                    'pspReference'        => 'psp_123',
                    'originalReference'   => '',
                    'merchantAccountCode' => 'TestMerchant',
                    'merchantReference'   => 'ref_123',
                    'amount'              => ['value' => 1000, 'currency' => 'EUR'],
                    'eventCode'           => 'AUTHORISATION',
                    'success'             => 'true',
                    'additionalData'      => ['hmacSignature' => 'invalid_signature'],
                ]],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('signature verification failed');

        $gateway->verifyWebhook($payload, [], '44782DEF547AAA06C910C43D1F16564A10667222A1A6D14BDEE26CAF7D28B2E4');
    }

    public function test_coinbase_commerce_refund_returns_failure(): void
    {
        $orderPayment = OrderPayment::factory()->create();

        $gateway = new CoinbaseCommerceGateway();
        $result  = $gateway->refund($orderPayment, 1000);

        $this->assertFalse($result->success);
        $this->assertEquals(OrderPaymentStatus::Failed, $result->status);
    }

    // === All Providers Have Gateways ===

    public function test_every_provider_enum_has_gateway(): void
    {
        foreach (PaymentProvider::cases() as $provider) {
            $gateway = PaymentGatewayFactory::makeFromProvider($provider);
            $this->assertInstanceOf(PaymentGatewayInterface::class, $gateway, "Missing gateway for {$provider->value}");
        }
    }
}
