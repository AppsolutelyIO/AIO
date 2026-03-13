<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class KlarnaGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://api.playground.klarna.com'
            : 'https://api.klarna.com';

        $region = $payment->setting['region'] ?? 'eu';
        if ($region === 'na') {
            $baseUrl = $payment->is_test_mode
                ? 'https://api-na.playground.klarna.com'
                : 'https://api-na.klarna.com';
        } elseif ($region === 'oc') {
            $baseUrl = $payment->is_test_mode
                ? 'https://api-oc.playground.klarna.com'
                : 'https://api-oc.klarna.com';
        }

        try {
            $currency    = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $countryCode = $payment->setting['country_code'] ?? 'US';

            $sessionParams = [
                'purchase_country'  => $countryCode,
                'purchase_currency' => $currency,
                'locale'            => $payment->setting['locale'] ?? 'en-US',
                'order_amount'      => $amount,
                'order_lines'       => [[
                    'name'         => $order->summary ?? "Order #{$order->reference}",
                    'quantity'     => 1,
                    'unit_price'   => $amount,
                    'total_amount' => $amount,
                ]],
                'merchant_urls' => [
                    'terms'        => config('app.url') . '/terms',
                    'checkout'     => config('app.url') . '/checkout',
                    'confirmation' => $options['success_url'] ?? config('app.url') . '/payment/success',
                    'push'         => $payment->webhook_url ?? config('app.url') . '/payment/klarna/webhook',
                ],
                'merchant_reference1' => $order->reference,
            ];

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/checkout/v3/orders", $sessionParams);

            $data = $response->json();

            if ($response->successful() && isset($data['html_snippet'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['html_snippet'],
                    vendorReference: $data['order_id'] ?? '',
                    vendorExtraInfo: [
                        'order_id'     => $data['order_id'] ?? '',
                        'html_snippet' => $data['html_snippet'],
                        'is_embedded'  => true,
                    ],
                );
            }

            $errorMsg = $data['error_message'] ?? $data['error_messages'][0] ?? 'Klarna session creation failed.';

            return PaymentGatewayResult::failure("Klarna error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Klarna error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;

            $baseUrl = $payment->is_test_mode
                ? 'https://api.playground.klarna.com'
                : 'https://api.klarna.com';

            $region = $payment->setting['region'] ?? 'eu';
            if ($region === 'na') {
                $baseUrl = str_replace('://api.', '://api-na.', $baseUrl);
            } elseif ($region === 'oc') {
                $baseUrl = str_replace('://api.', '://api-oc.', $baseUrl);
            }

            $orderId = $orderPayment->vendor_extra_info['order_id'] ?? $orderPayment->vendor_reference;

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/ordermanagement/v1/orders/{$orderId}/refunds", [
                    'refunded_amount' => $amount,
                    'description'     => $reason ?? 'Refund requested',
                ]);

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $orderId,
                    vendorExtraInfo: ['status' => 'refunded'],
                );
            }

            $data = $response->json();

            return PaymentGatewayResult::failure('Klarna refund error: ' . ($data['error_message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Klarna refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        // Klarna uses basic auth for push notifications, validated at HTTP level.
        // The secret parameter here is expected to be the merchant secret for verification.
        $data = json_decode($payload, true);

        if (! $data || ! isset($data['order_id'])) {
            throw new \RuntimeException('Klarna webhook verification failed: invalid payload.');
        }

        return [
            'event'            => $data['event_type'] ?? 'push',
            'vendor_reference' => $data['order_id'] ?? '',
            'status'           => $data['status'] ?? '',
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
        return 'Klarna';
    }
}
