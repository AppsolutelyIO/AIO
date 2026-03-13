<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum PaymentProvider: string
{
    case Stripe            = 'stripe';
    case Paypal            = 'paypal';
    case Bank              = 'bank';
    case Manual            = 'manual';
    case Alipay            = 'alipay';
    case WechatPay         = 'wechat_pay';
    case Crypto            = 'crypto';
    case Creem             = 'creem';
    case Paddle            = 'paddle';
    case LemonSqueezy      = 'lemon_squeezy';
    case Klarna            = 'klarna';
    case Afterpay          = 'afterpay';
    case Square            = 'square';
    case Adyen             = 'adyen';
    case Mollie            = 'mollie';
    case Razorpay          = 'razorpay';
    case MercadoPago       = 'mercado_pago';
    case CoinbaseCommerce  = 'coinbase_commerce';
    case BtcpayServer      = 'btcpay_server';
    case ApplePay          = 'apple_pay';
    case GooglePay         = 'google_pay';

    public function label(): string
    {
        return match ($this) {
            self::Stripe           => 'Stripe',
            self::Paypal           => 'PayPal',
            self::Bank             => 'Bank Transfer',
            self::Manual           => 'Manual / Offline',
            self::Alipay           => 'Alipay',
            self::WechatPay        => 'WeChat Pay',
            self::Crypto           => 'Cryptocurrency',
            self::Creem            => 'Creem',
            self::Paddle           => 'Paddle',
            self::LemonSqueezy     => 'Lemon Squeezy',
            self::Klarna           => 'Klarna',
            self::Afterpay         => 'Afterpay / Clearpay',
            self::Square           => 'Square',
            self::Adyen            => 'Adyen',
            self::Mollie           => 'Mollie',
            self::Razorpay         => 'Razorpay',
            self::MercadoPago      => 'MercadoPago',
            self::CoinbaseCommerce => 'Coinbase Commerce',
            self::BtcpayServer     => 'BTCPay Server',
            self::ApplePay         => 'Apple Pay',
            self::GooglePay        => 'Google Pay',
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
