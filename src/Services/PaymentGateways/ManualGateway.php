<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\Payment;

final class ManualGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        return PaymentGatewayResult::pending(
            vendorReference: "MANUAL-{$order->reference}",
            vendorExtraInfo: [
                'instruction' => $payment->instruction,
                'amount'      => $amount,
            ],
        );
    }

    public function getName(): string
    {
        return 'Manual / Offline';
    }
}
