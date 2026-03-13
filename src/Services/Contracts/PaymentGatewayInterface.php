<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment (create checkout session, redirect URL, etc.).
     *
     * @param  array<string, mixed>  $options  Gateway-specific options
     */
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult;

    /**
     * Process a refund for a completed payment.
     *
     * @param  int  $amount  Amount to refund in smallest currency unit
     */
    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult;

    /**
     * Verify a webhook payload and return the parsed result.
     *
     * @param  array<string, string>  $headers  Request headers
     * @return array{event: string, vendor_reference: string, status: string, data: array<string, mixed>}
     */
    public function verifyWebhook(string $payload, array $headers, string $secret): array;

    /**
     * Whether this gateway supports refunds.
     */
    public function supportsRefund(): bool;

    /**
     * Whether this gateway requires a redirect (hosted checkout).
     */
    public function requiresRedirect(): bool;

    /**
     * Get the gateway display name.
     */
    public function getName(): string;
}
