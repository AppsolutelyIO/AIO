<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\Payment;

/**
 * Google Pay gateway backed by a third-party processor (Stripe, Adyen, etc.).
 *
 * Google Pay itself is not a standalone payment gateway — it requires a processor.
 * This gateway delegates to the configured backing provider while presenting
 * Google Pay as the payment method to the customer.
 */
final class GooglePayGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $backingProvider = $payment->setting['backing_provider'] ?? 'stripe';

        try {
            $options['payment_method_types'] = ['card'];
            $options['wallet_type']          = 'google_pay';

            $gateway = PaymentGatewayFactory::makeFromProvider(
                \Appsolutely\AIO\Enums\PaymentProvider::from($backingProvider)
            );

            return $gateway->charge($order, $payment, $amount, $options);
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Google Pay error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        // Delegates to backing provider's webhook verification
        throw new \RuntimeException('Google Pay webhook verification should be handled by the backing provider.');
    }

    public function supportsRefund(): bool
    {
        return true;
    }

    public function requiresRedirect(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'Google Pay';
    }
}
