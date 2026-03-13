<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class MollieGateway extends AbstractGateway
{
    private const API_URL = 'https://api.mollie.com/v2';

    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        try {
            $currency      = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $amountDecimal = number_format($amount / 100, 2, '.', '');

            $paymentParams = [
                'amount' => [
                    'currency' => $currency,
                    'value'    => $amountDecimal,
                ],
                'description' => $order->summary ?? "Order #{$order->reference}",
                'redirectUrl' => $options['success_url'] ?? config('app.url') . '/payment/success',
                'webhookUrl'  => $payment->webhook_url ?? config('app.url') . '/payment/mollie/webhook',
                'metadata'    => [
                    'order_id'   => (string) $order->id,
                    'order_ref'  => $order->reference,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            $response = Http::withToken($payment->merchant_secret)
                ->post(self::API_URL . '/payments', $paymentParams);

            $data = $response->json();

            if ($response->successful() && isset($data['_links']['checkout']['href'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['_links']['checkout']['href'],
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'mollie_payment_id' => $data['id'] ?? '',
                        'status'            => $data['status'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['detail'] ?? $data['title'] ?? 'Mollie payment creation failed.';

            return PaymentGatewayResult::failure("Mollie error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Mollie error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment       = $orderPayment->payment;
            $mollieId      = $orderPayment->vendor_extra_info['mollie_payment_id'] ?? $orderPayment->vendor_reference;
            $currency      = strtoupper($payment->currency ?? 'USD');
            $amountDecimal = number_format($amount / 100, 2, '.', '');

            $response = Http::withToken($payment->merchant_secret)
                ->post(self::API_URL . "/payments/{$mollieId}/refunds", [
                    'amount' => [
                        'currency' => $currency,
                        'value'    => $amountDecimal,
                    ],
                    'description' => $reason ?? 'Refund requested',
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: ['refund_id' => $data['id'] ?? '', 'status' => $data['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Mollie refund error: ' . ($data['detail'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Mollie refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        // Mollie webhooks send only the payment ID. We need to fetch the payment to verify.
        // The secret is used as the API key to fetch and verify the payment.
        parse_str($payload, $params);
        $paymentId = $params['id'] ?? '';

        if (empty($paymentId)) {
            throw new \RuntimeException('Mollie webhook verification failed: missing payment ID.');
        }

        try {
            $response = Http::withToken($secret)
                ->get(self::API_URL . "/payments/{$paymentId}");

            $data = $response->json();

            if (! $response->successful()) {
                throw new \RuntimeException('Mollie webhook verification failed: could not fetch payment.');
            }

            return [
                'event'            => 'payment.' . ($data['status'] ?? 'unknown'),
                'vendor_reference' => $data['id'] ?? '',
                'status'           => $data['status'] ?? '',
                'data'             => $data,
            ];
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Mollie webhook verification failed: {$e->getMessage()}");
        }
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
        return 'Mollie';
    }
}
