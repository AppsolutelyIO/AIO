<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\PaymentMethod;
use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(PaymentProvider::cases());

        return [
            'reference'            => (string) Str::ulid(),
            'title'                => $provider->label(),
            'display'              => $provider->label(),
            'vendor'               => $provider->value,
            'provider'             => $provider,
            'payment_method'       => fake()->randomElement(PaymentMethod::cases()),
            'handler'              => null,
            'device'               => 'web',
            'currency'             => 'USD',
            'supported_currencies' => ['USD'],
            'merchant_id'          => null,
            'merchant_key'         => null,
            'merchant_secret'      => null,
            'setting'              => [],
            'is_test_mode'         => true,
            'webhook_url'          => null,
            'fee_percentage'       => 0,
            'fee_fixed'            => 0,
            'min_amount'           => 0,
            'max_amount'           => 0,
            'instruction'          => null,
            'remark'               => null,
            'sort'                 => 0,
            'status'               => Status::ACTIVE,
        ];
    }

    /**
     * Indicate that the payment is for Stripe.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Stripe',
            'display'        => 'Credit Card',
            'vendor'         => 'stripe',
            'provider'       => PaymentProvider::Stripe,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 2.9,
            'fee_fixed'      => 30,
        ]);
    }

    /**
     * Indicate that the payment is for PayPal.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'PayPal',
            'display'        => 'PayPal',
            'vendor'         => 'paypal',
            'provider'       => PaymentProvider::Paypal,
            'payment_method' => PaymentMethod::EWallet,
            'fee_percentage' => 3.49,
            'fee_fixed'      => 49,
        ]);
    }

    /**
     * Indicate that the payment is for bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Bank Transfer',
            'display'        => 'Bank Transfer',
            'vendor'         => 'bank',
            'provider'       => PaymentProvider::Bank,
            'payment_method' => PaymentMethod::BankTransfer,
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for Creem.
     */
    public function creem(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Creem',
            'display'        => 'Creem',
            'vendor'         => 'creem',
            'provider'       => PaymentProvider::Creem,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for Paddle.
     */
    public function paddle(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Paddle',
            'display'        => 'Paddle',
            'vendor'         => 'paddle',
            'provider'       => PaymentProvider::Paddle,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 5,
            'fee_fixed'      => 50,
        ]);
    }

    /**
     * Indicate that the payment is for Lemon Squeezy.
     */
    public function lemonSqueezy(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Lemon Squeezy',
            'display'        => 'Lemon Squeezy',
            'vendor'         => 'lemon_squeezy',
            'provider'       => PaymentProvider::LemonSqueezy,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 5,
            'fee_fixed'      => 50,
        ]);
    }

    /**
     * Indicate that the payment is for Klarna.
     */
    public function klarna(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Klarna',
            'display'        => 'Buy Now, Pay Later',
            'vendor'         => 'klarna',
            'provider'       => PaymentProvider::Klarna,
            'payment_method' => PaymentMethod::BuyNowPayLater,
            'fee_percentage' => 3.29,
            'fee_fixed'      => 30,
        ]);
    }

    /**
     * Indicate that the payment is for Afterpay.
     */
    public function afterpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Afterpay / Clearpay',
            'display'        => 'Buy Now, Pay Later',
            'vendor'         => 'afterpay',
            'provider'       => PaymentProvider::Afterpay,
            'payment_method' => PaymentMethod::BuyNowPayLater,
            'fee_percentage' => 6,
            'fee_fixed'      => 30,
        ]);
    }

    /**
     * Indicate that the payment is for Square.
     */
    public function square(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Square',
            'display'        => 'Square',
            'vendor'         => 'square',
            'provider'       => PaymentProvider::Square,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 2.9,
            'fee_fixed'      => 30,
        ]);
    }

    /**
     * Indicate that the payment is for Adyen.
     */
    public function adyen(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Adyen',
            'display'        => 'Adyen',
            'vendor'         => 'adyen',
            'provider'       => PaymentProvider::Adyen,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 3,
            'fee_fixed'      => 12,
        ]);
    }

    /**
     * Indicate that the payment is for Mollie.
     */
    public function mollie(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Mollie',
            'display'        => 'Mollie',
            'vendor'         => 'mollie',
            'provider'       => PaymentProvider::Mollie,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 1.8,
            'fee_fixed'      => 25,
        ]);
    }

    /**
     * Indicate that the payment is for Razorpay.
     */
    public function razorpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Razorpay',
            'display'        => 'Razorpay',
            'vendor'         => 'razorpay',
            'provider'       => PaymentProvider::Razorpay,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 2,
            'fee_fixed'      => 0,
            'currency'       => 'INR',
        ]);
    }

    /**
     * Indicate that the payment is for MercadoPago.
     */
    public function mercadoPago(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'MercadoPago',
            'display'        => 'MercadoPago',
            'vendor'         => 'mercado_pago',
            'provider'       => PaymentProvider::MercadoPago,
            'payment_method' => PaymentMethod::CreditCard,
            'fee_percentage' => 4.99,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for Coinbase Commerce.
     */
    public function coinbaseCommerce(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Coinbase Commerce',
            'display'        => 'Coinbase Commerce',
            'vendor'         => 'coinbase_commerce',
            'provider'       => PaymentProvider::CoinbaseCommerce,
            'payment_method' => PaymentMethod::Crypto,
            'fee_percentage' => 1,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for BTCPay Server.
     */
    public function btcpayServer(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'BTCPay Server',
            'display'        => 'BTCPay Server',
            'vendor'         => 'btcpay_server',
            'provider'       => PaymentProvider::BtcpayServer,
            'payment_method' => PaymentMethod::Crypto,
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for Apple Pay (third-party backed).
     */
    public function applePay(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Apple Pay',
            'display'        => 'Apple Pay',
            'vendor'         => 'apple_pay',
            'provider'       => PaymentProvider::ApplePay,
            'payment_method' => PaymentMethod::EWallet,
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is for Google Pay (third-party backed).
     */
    public function googlePay(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => 'Google Pay',
            'display'        => 'Google Pay',
            'vendor'         => 'google_pay',
            'provider'       => PaymentProvider::GooglePay,
            'payment_method' => PaymentMethod::EWallet,
            'fee_percentage' => 0,
            'fee_fixed'      => 0,
        ]);
    }

    /**
     * Indicate that the payment is in test mode.
     */
    public function testMode(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_test_mode' => true,
        ]);
    }

    /**
     * Indicate that the payment is in live mode.
     */
    public function liveMode(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_test_mode' => false,
        ]);
    }
}
