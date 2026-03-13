<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Services\Contracts\PaymentGatewayInterface;

final class PaymentGatewayFactory
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    private static array $gateways = [
        'stripe'            => StripeGateway::class,
        'paypal'            => PaypalGateway::class,
        'bank'              => BankTransferGateway::class,
        'manual'            => ManualGateway::class,
        'alipay'            => AlipayGateway::class,
        'wechat_pay'        => WechatPayGateway::class,
        'crypto'            => CryptoGateway::class,
        'creem'             => CreemGateway::class,
        'paddle'            => PaddleGateway::class,
        'lemon_squeezy'     => LemonSqueezyGateway::class,
        'klarna'            => KlarnaGateway::class,
        'afterpay'          => AfterpayGateway::class,
        'square'            => SquareGateway::class,
        'adyen'             => AdyenGateway::class,
        'mollie'            => MollieGateway::class,
        'razorpay'          => RazorpayGateway::class,
        'mercado_pago'      => MercadoPagoGateway::class,
        'coinbase_commerce' => CoinbaseCommerceGateway::class,
        'btcpay_server'     => BtcpayServerGateway::class,
        'apple_pay'         => ApplePayGateway::class,
        'google_pay'        => GooglePayGateway::class,
    ];

    public static function make(Payment $payment): PaymentGatewayInterface
    {
        return self::makeFromProvider($payment->provider);
    }

    public static function makeFromProvider(PaymentProvider $provider): PaymentGatewayInterface
    {
        $gatewayClass = self::$gateways[$provider->value] ?? null;

        if ($gatewayClass === null) {
            throw new \InvalidArgumentException("No gateway implementation for provider: {$provider->value}");
        }

        return new $gatewayClass();
    }

    /**
     * @return array<string, class-string<PaymentGatewayInterface>>
     */
    public static function getRegisteredGateways(): array
    {
        return self::$gateways;
    }

    /**
     * Register a custom gateway for a provider.
     *
     * @param  class-string<PaymentGatewayInterface>  $gatewayClass
     */
    public static function register(string $providerValue, string $gatewayClass): void
    {
        self::$gateways[$providerValue] = $gatewayClass;
    }
}
