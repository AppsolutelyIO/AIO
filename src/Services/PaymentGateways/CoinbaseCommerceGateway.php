<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class CoinbaseCommerceGateway extends AbstractGateway
{
    private const API_URL = 'https://api.commerce.coinbase.com';

    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        try {
            $currency      = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $amountDecimal = number_format($amount / 100, 2, '.', '');

            $chargeParams = [
                'name'         => $order->summary ?? "Order #{$order->reference}",
                'description'  => "Payment for Order #{$order->reference}",
                'pricing_type' => 'fixed_price',
                'local_price'  => [
                    'amount'   => $amountDecimal,
                    'currency' => $currency,
                ],
                'metadata' => [
                    'order_id'   => (string) $order->id,
                    'order_ref'  => $order->reference,
                    'payment_id' => (string) $payment->id,
                ],
                'redirect_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                'cancel_url'   => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
            ];

            $response = Http::withHeaders([
                'X-CC-Api-Key' => $payment->merchant_secret,
                'X-CC-Version' => '2018-03-22',
            ])->post(self::API_URL . '/charges', $chargeParams);

            $data = $response->json();

            if ($response->successful() && isset($data['data']['hosted_url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['data']['hosted_url'],
                    vendorReference: $data['data']['code'] ?? $data['data']['id'] ?? '',
                    vendorExtraInfo: [
                        'charge_id'   => $data['data']['id'] ?? '',
                        'charge_code' => $data['data']['code'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['error']['message'] ?? 'Coinbase Commerce charge creation failed.';

            return PaymentGatewayResult::failure("Coinbase Commerce error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Coinbase Commerce error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['x-cc-webhook-signature'] ?? $headers['X-Cc-Webhook-Signature'] ?? '';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Coinbase Commerce webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        $event     = $data['event'] ?? [];
        $timeline  = $event['data']['timeline'] ?? [];
        $lastEvent = end($timeline);

        return [
            'event'            => $event['type'] ?? '',
            'vendor_reference' => $event['data']['code'] ?? $event['data']['id'] ?? '',
            'status'           => $lastEvent['status'] ?? '',
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
        return 'Coinbase Commerce';
    }
}
