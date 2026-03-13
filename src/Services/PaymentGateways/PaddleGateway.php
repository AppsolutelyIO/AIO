<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class PaddleGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';

        try {
            $currency = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));

            $transactionParams = [
                'items' => [[
                    'price' => [
                        'description' => $order->summary ?? "Order #{$order->reference}",
                        'name'        => $order->summary ?? "Order #{$order->reference}",
                        'unit_price'  => [
                            'amount'        => (string) $amount,
                            'currency_code' => $currency,
                        ],
                        'quantity' => [
                            'minimum' => 1,
                            'maximum' => 1,
                        ],
                        'product_id' => $options['product_id'] ?? $payment->setting['product_id'] ?? null,
                    ],
                    'quantity' => 1,
                ]],
                'checkout' => [
                    'url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                ],
                'custom_data' => [
                    'order_id'   => (string) $order->id,
                    'order_ref'  => $order->reference,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/transactions", $transactionParams);

            $data = $response->json();

            if ($response->successful() && isset($data['data']['checkout']['url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['data']['checkout']['url'],
                    vendorReference: $data['data']['id'] ?? '',
                    vendorExtraInfo: [
                        'transaction_id' => $data['data']['id'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['error']['detail'] ?? $data['error']['type'] ?? 'Paddle transaction creation failed.';

            return PaymentGatewayResult::failure("Paddle error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Paddle error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $baseUrl = $payment->is_test_mode
                ? 'https://sandbox-api.paddle.com'
                : 'https://api.paddle.com';

            $transactionId = $orderPayment->vendor_extra_info['transaction_id'] ?? $orderPayment->vendor_reference;

            $refundParams = [
                'transaction_id' => $transactionId,
                'reason'         => $reason ?? 'Refund requested',
            ];

            if ($amount > 0) {
                $refundParams['items'] = [[
                    'type'   => 'partial',
                    'amount' => (string) $amount,
                ]];
            }

            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/adjustments", $refundParams);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['data']['id'] ?? '',
                    vendorExtraInfo: ['adjustment_id' => $data['data']['id'] ?? '', 'status' => $data['data']['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Paddle refund error: ' . ($data['error']['detail'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Paddle refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['paddle-signature'] ?? $headers['Paddle-Signature'] ?? '';

        if (empty($signature)) {
            throw new \RuntimeException('Paddle webhook signature missing.');
        }

        // Parse ts and h1 from Paddle-Signature header: ts=xxx;h1=xxx
        $parts = [];
        foreach (explode(';', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key]   = $value;
        }

        $ts = $parts['ts'] ?? '';
        $h1 = $parts['h1'] ?? '';

        $expectedSignature = hash_hmac('sha256', "{$ts}:{$payload}", $secret);

        if (! hash_equals($expectedSignature, $h1)) {
            throw new \RuntimeException('Paddle webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['event_type'] ?? '',
            'vendor_reference' => $data['data']['id'] ?? '',
            'status'           => $data['data']['status'] ?? '',
            'data'             => $data,
        ];
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
        return 'Paddle';
    }
}
