<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class MercadoPagoGateway extends AbstractGateway
{
    private const API_URL = 'https://api.mercadopago.com';

    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        try {
            $currency      = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'BRL'));
            $amountDecimal = round($amount / 100, 2);

            $preferenceParams = [
                'items' => [[
                    'title'       => $order->summary ?? "Order #{$order->reference}",
                    'quantity'    => 1,
                    'unit_price'  => $amountDecimal,
                    'currency_id' => $currency,
                ]],
                'back_urls' => [
                    'success' => $options['success_url'] ?? config('app.url') . '/payment/success',
                    'failure' => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
                    'pending' => config('app.url') . '/payment/pending',
                ],
                'auto_return'        => 'approved',
                'external_reference' => $order->reference,
                'notification_url'   => $payment->webhook_url ?? config('app.url') . '/payment/mercadopago/webhook',
                'metadata'           => [
                    'order_id'   => (string) $order->id,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            $response = Http::withToken($payment->merchant_secret)
                ->post(self::API_URL . '/checkout/preferences', $preferenceParams);

            $data = $response->json();

            $redirectUrl = $payment->is_test_mode
                ? ($data['sandbox_init_point'] ?? null)
                : ($data['init_point'] ?? null);

            if ($response->successful() && $redirectUrl) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $redirectUrl,
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'preference_id' => $data['id'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['message'] ?? $data['error'] ?? 'MercadoPago preference creation failed.';

            return PaymentGatewayResult::failure("MercadoPago error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("MercadoPago error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment     = $orderPayment->payment;
            $mpPaymentId = $orderPayment->vendor_extra_info['mp_payment_id'] ?? $orderPayment->vendor_reference;

            $response = Http::withToken($payment->merchant_secret)
                ->post(self::API_URL . "/v1/payments/{$mpPaymentId}/refunds", [
                    'amount' => round($amount / 100, 2),
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: (string) ($data['id'] ?? ''),
                    vendorExtraInfo: ['refund_id' => $data['id'] ?? '', 'status' => $data['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('MercadoPago refund error: ' . ($data['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("MercadoPago refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        // MercadoPago uses x-signature header with ts and v1 hash
        $xSignature  = $headers['x-signature'] ?? $headers['X-Signature'] ?? '';
        $xRequestId  = $headers['x-request-id'] ?? $headers['X-Request-Id'] ?? '';

        $data   = json_decode($payload, true);
        $dataId = (string) ($data['data']['id'] ?? '');

        // Parse ts and v1 from x-signature: ts=xxx,v1=xxx
        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $ts = $parts['ts'] ?? '';
        $v1 = $parts['v1'] ?? '';

        $manifest     = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        if (! hash_equals($expectedHash, $v1)) {
            throw new \RuntimeException('MercadoPago webhook signature verification failed.');
        }

        return [
            'event'            => $data['type'] ?? $data['action'] ?? '',
            'vendor_reference' => $dataId,
            'status'           => $data['action'] ?? '',
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
        return 'MercadoPago';
    }
}
