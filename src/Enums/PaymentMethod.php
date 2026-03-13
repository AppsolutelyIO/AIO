<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum PaymentMethod: string
{
    case CreditCard     = 'credit_card';
    case DebitCard      = 'debit_card';
    case BankTransfer   = 'bank_transfer';
    case EWallet        = 'e_wallet';
    case QrCode         = 'qr_code';
    case Crypto         = 'crypto';
    case CashOnDelivery = 'cash_on_delivery';
    case BuyNowPayLater = 'buy_now_pay_later';

    public function label(): string
    {
        return match ($this) {
            self::CreditCard     => 'Credit Card',
            self::DebitCard      => 'Debit Card',
            self::BankTransfer   => 'Bank Transfer',
            self::EWallet        => 'E-Wallet',
            self::QrCode         => 'QR Code',
            self::Crypto         => 'Cryptocurrency',
            self::CashOnDelivery => 'Cash on Delivery',
            self::BuyNowPayLater => 'Buy Now, Pay Later',
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
