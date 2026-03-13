<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class BtcpayServerGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        try {
            $baseUrl       = rtrim($payment->setting['server_url'] ?? '', '/');
            $storeId       = $payment->merchant_id;
            $currency      = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $amountDecimal = number_format($amount / 100, 2, '.', '');

            $invoiceParams = [
                'amount'   => $amountDecimal,
                'currency' => $currency,
                'metadata' => [
                    'orderId'    => $order->reference,
                    'order_id'   => (string) $order->id,
                    'payment_id' => (string) $payment->id,
                ],
                'checkout' => [
                    'redirectURL'           => $options['success_url'] ?? config('app.url') . '/payment/success',
                    'redirectAutomatically' => true,
                ],
                'notificationUrl' => $payment->webhook_url ?? config('app.url') . '/payment/btcpay/webhook',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'token ' . $payment->merchant_secret,
            ])->post("{$baseUrl}/api/v1/stores/{$storeId}/invoices", $invoiceParams);

            $data = $response->json();

            if ($response->successful() && isset($data['checkoutLink'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['checkoutLink'],
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'invoice_id'    => $data['id'] ?? '',
                        'checkout_link' => $data['checkoutLink'],
                    ],
                );
            }

            $errorMsg = is_string($data) ? $data : ($data['message'] ?? json_encode($data['errors'] ?? 'BTCPay invoice creation failed.'));

            return PaymentGatewayResult::failure("BTCPay Server error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("BTCPay Server error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment   = $orderPayment->payment;
            $baseUrl   = rtrim($payment->setting['server_url'] ?? '', '/');
            $storeId   = $payment->merchant_id;
            $invoiceId = $orderPayment->vendor_extra_info['invoice_id'] ?? $orderPayment->vendor_reference;

            $response = Http::withHeaders([
                'Authorization' => 'token ' . $payment->merchant_secret,
            ])->post("{$baseUrl}/api/v1/stores/{$storeId}/invoices/{$invoiceId}/refund", [
                'paymentMethod' => 'BTC',
                'description'   => $reason ?? 'Refund requested',
            ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['refundId'] ?? $invoiceId,
                    vendorExtraInfo: ['status' => 'refund_initiated'],
                );
            }

            return PaymentGatewayResult::failure('BTCPay Server refund error: ' . ($data['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("BTCPay Server refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['btcpay-sig'] ?? $headers['BTCPay-Sig'] ?? '';

        // BTCPay sends: sha256=HASH
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('BTCPay Server webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['type'] ?? '',
            'vendor_reference' => $data['invoiceId'] ?? $data['storeId'] ?? '',
            'status'           => $data['type'] ?? '',
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
        return 'BTCPay Server';
    }
}
