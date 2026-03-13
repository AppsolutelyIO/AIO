<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class LemonSqueezyGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = 'https://api.lemonsqueezy.com/v1';

        try {
            $storeId   = $payment->setting['store_id'] ?? '';
            $variantId = $options['variant_id'] ?? $payment->setting['variant_id'] ?? '';

            $checkoutParams = [
                'data' => [
                    'type'       => 'checkouts',
                    'attributes' => [
                        'custom_price'    => $amount,
                        'product_options' => [
                            'redirect_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                        ],
                        'checkout_data' => [
                            'custom' => [
                                'order_id'   => (string) $order->id,
                                'order_ref'  => $order->reference,
                                'payment_id' => (string) $payment->id,
                            ],
                        ],
                    ],
                    'relationships' => [
                        'store'   => ['data' => ['type' => 'stores', 'id' => $storeId]],
                        'variant' => ['data' => ['type' => 'variants', 'id' => $variantId]],
                    ],
                ],
            ];

            $response = Http::withToken($payment->merchant_secret)
                ->withHeaders(['Accept' => 'application/vnd.api+json', 'Content-Type' => 'application/vnd.api+json'])
                ->post("{$baseUrl}/checkouts", $checkoutParams);

            $data = $response->json();

            if ($response->successful() && isset($data['data']['attributes']['url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['data']['attributes']['url'],
                    vendorReference: $data['data']['id'] ?? '',
                    vendorExtraInfo: ['checkout_id' => $data['data']['id'] ?? ''],
                );
            }

            $errorMsg = $data['errors'][0]['detail'] ?? 'Lemon Squeezy checkout creation failed.';

            return PaymentGatewayResult::failure("Lemon Squeezy error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Lemon Squeezy error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $orderId = $orderPayment->vendor_extra_info['lemon_order_id'] ?? $orderPayment->vendor_reference;

            $response = Http::withToken($payment->merchant_secret)
                ->withHeaders(['Accept' => 'application/vnd.api+json', 'Content-Type' => 'application/vnd.api+json'])
                ->post("https://api.lemonsqueezy.com/v1/orders/{$orderId}/refund", [
                    'data' => [
                        'type'       => 'orders',
                        'id'         => $orderId,
                        'attributes' => [
                            'amount' => $amount,
                        ],
                    ],
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['data']['id'] ?? $orderId,
                    vendorExtraInfo: ['status' => $data['data']['attributes']['status'] ?? 'refunded'],
                );
            }

            return PaymentGatewayResult::failure('Lemon Squeezy refund error: ' . ($data['errors'][0]['detail'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Lemon Squeezy refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['x-signature'] ?? $headers['X-Signature'] ?? '';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Lemon Squeezy webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['meta']['event_name'] ?? '',
            'vendor_reference' => (string) ($data['data']['id'] ?? ''),
            'status'           => $data['data']['attributes']['status'] ?? '',
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
        return 'Lemon Squeezy';
    }
}
