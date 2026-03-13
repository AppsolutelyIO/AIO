<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class CreemGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://test-api.creem.io/v1'
            : 'https://api.creem.io/v1';

        try {
            $productId = $options['product_id'] ?? $payment->setting['product_id'] ?? null;

            $checkoutParams = [
                'product_id'  => $productId,
                'success_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                'metadata'    => [
                    'order_id'   => (string) $order->id,
                    'order_ref'  => $order->reference,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            if (isset($options['request_id'])) {
                $checkoutParams['request_id'] = $options['request_id'];
            }

            $response = Http::withHeaders([
                'x-api-key' => $payment->merchant_secret,
            ])->post("{$baseUrl}/checkouts", $checkoutParams);

            $data = $response->json();

            if ($response->successful() && isset($data['checkout_url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['checkout_url'],
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'checkout_id'  => $data['id'] ?? '',
                        'checkout_url' => $data['checkout_url'],
                    ],
                );
            }

            $errorMsg = $data['message'] ?? $data['error'] ?? 'Creem checkout creation failed.';
            if (is_array($errorMsg)) {
                $errorMsg = implode('; ', $errorMsg);
            }

            return PaymentGatewayResult::failure("Creem error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Creem error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        // Creem handles refunds through their dashboard or the refund.created webhook event.
        // API-initiated refunds are managed through the Creem dashboard.
        return PaymentGatewayResult::failure('Creem refunds must be initiated through the Creem dashboard.');
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['creem-signature'] ?? $headers['Creem-Signature'] ?? '';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Creem webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['eventType'] ?? '',
            'vendor_reference' => $data['object']['id'] ?? '',
            'status'           => $data['object']['status'] ?? '',
            'data'             => $data,
        ];
    }

    public function supportsRefund(): bool
    {
        return false;
    }

    public function requiresRedirect(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'Creem';
    }
}
