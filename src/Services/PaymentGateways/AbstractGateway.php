<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Services\Contracts\PaymentGatewayInterface;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure("Refund not supported by {$this->getName()}.");
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        throw new \RuntimeException("Webhook verification not supported by {$this->getName()}.");
    }

    public function supportsRefund(): bool
    {
        return false;
    }

    public function requiresRedirect(): bool
    {
        return false;
    }
}
