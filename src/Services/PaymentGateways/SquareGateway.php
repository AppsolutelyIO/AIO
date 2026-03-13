<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class SquareGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://connect.squareupsandbox.com/v2'
            : 'https://connect.squareup.com/v2';

        try {
            $currency   = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $locationId = $payment->setting['location_id'] ?? '';

            $checkoutParams = [
                'idempotency_key' => Str::uuid()->toString(),
                'order'           => [
                    'location_id' => $locationId,
                    'line_items'  => [[
                        'name'             => $order->summary ?? "Order #{$order->reference}",
                        'quantity'         => '1',
                        'base_price_money' => [
                            'amount'   => $amount,
                            'currency' => $currency,
                        ],
                    ]],
                    'reference_id' => $order->reference,
                ],
                'checkout_options' => [
                    'redirect_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                ],
            ];

            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/online-checkout/payment-links", $checkoutParams);

            $data = $response->json();

            if ($response->successful() && isset($data['payment_link']['url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['payment_link']['url'],
                    vendorReference: $data['payment_link']['id'] ?? '',
                    vendorExtraInfo: [
                        'payment_link_id' => $data['payment_link']['id'] ?? '',
                        'order_id'        => $data['payment_link']['order_id'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['errors'][0]['detail'] ?? 'Square checkout creation failed.';

            return PaymentGatewayResult::failure("Square error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Square error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $baseUrl = $payment->is_test_mode
                ? 'https://connect.squareupsandbox.com/v2'
                : 'https://connect.squareup.com/v2';

            $paymentId = $orderPayment->vendor_extra_info['square_payment_id'] ?? $orderPayment->vendor_reference;
            $currency  = strtoupper($payment->currency ?? 'USD');

            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/refunds", [
                    'idempotency_key' => Str::uuid()->toString(),
                    'payment_id'      => $paymentId,
                    'amount_money'    => [
                        'amount'   => $amount,
                        'currency' => $currency,
                    ],
                    'reason' => $reason ?? 'Refund requested',
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['refund'])) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['refund']['id'] ?? '',
                    vendorExtraInfo: ['refund_id' => $data['refund']['id'] ?? '', 'status' => $data['refund']['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Square refund error: ' . ($data['errors'][0]['detail'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Square refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature       = $headers['x-square-hmacsha256-signature'] ?? $headers['X-Square-HmacSha256-Signature'] ?? '';
        $notificationUrl = $headers['x-square-notification-url'] ?? $headers['X-Square-Notification-URL'] ?? '';

        $expectedSignature = base64_encode(hash_hmac('sha256', $notificationUrl . $payload, $secret, true));

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Square webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['type'] ?? '',
            'vendor_reference' => $data['data']['id'] ?? '',
            'status'           => $data['data']['object'][$data['data']['type'] ?? '']['status'] ?? '',
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
        return 'Square';
    }
}
