<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\Payment;

final class BankTransferGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        return PaymentGatewayResult::pending(
            vendorReference: "BANK-{$order->reference}",
            vendorExtraInfo: [
                'instruction'  => $payment->instruction,
                'bank_account' => $payment->setting['bank_account'] ?? null,
                'bank_name'    => $payment->setting['bank_name'] ?? null,
                'account_name' => $payment->setting['account_name'] ?? null,
                'amount'       => $amount,
            ],
        );
    }

    public function getName(): string
    {
        return 'Bank Transfer';
    }
}
