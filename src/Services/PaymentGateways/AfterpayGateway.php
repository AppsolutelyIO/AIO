<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class AfterpayGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://global-api-sandbox.afterpay.com/v2'
            : 'https://global-api.afterpay.com/v2';

        try {
            $currency      = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $amountDecimal = number_format($amount / 100, 2, '.', '');

            $checkoutParams = [
                'amount' => [
                    'amount'   => $amountDecimal,
                    'currency' => $currency,
                ],
                'consumer' => $options['consumer'] ?? [],
                'merchant' => [
                    'redirectConfirmUrl' => $options['success_url'] ?? config('app.url') . '/payment/success',
                    'redirectCancelUrl'  => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
                ],
                'merchantReference' => $order->reference,
            ];

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/checkouts", $checkoutParams);

            $data = $response->json();

            if ($response->successful() && isset($data['redirectCheckoutUrl'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['redirectCheckoutUrl'],
                    vendorReference: $data['token'] ?? '',
                    vendorExtraInfo: [
                        'token'        => $data['token'] ?? '',
                        'checkout_url' => $data['redirectCheckoutUrl'],
                    ],
                );
            }

            $errorMsg = $data['message'] ?? $data['errorCode'] ?? 'Afterpay checkout creation failed.';

            return PaymentGatewayResult::failure("Afterpay error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Afterpay error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $baseUrl = $payment->is_test_mode
                ? 'https://global-api-sandbox.afterpay.com/v2'
                : 'https://global-api.afterpay.com/v2';

            $afterpayOrderId = $orderPayment->vendor_extra_info['afterpay_order_id'] ?? $orderPayment->vendor_reference;
            $currency        = strtoupper($payment->currency ?? 'USD');
            $amountDecimal   = number_format($amount / 100, 2, '.', '');

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/payments/{$afterpayOrderId}/refund", [
                    'amount' => [
                        'amount'   => $amountDecimal,
                        'currency' => $currency,
                    ],
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['refundId'] ?? $afterpayOrderId,
                    vendorExtraInfo: ['refund_id' => $data['refundId'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Afterpay refund error: ' . ($data['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Afterpay refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        // Afterpay uses HMAC-SHA256 with X-Afterpay-Request-Signature header
        $signature = $headers['x-afterpay-request-signature'] ?? $headers['X-Afterpay-Request-Signature'] ?? '';

        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Afterpay webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['type'] ?? '',
            'vendor_reference' => (string) ($data['data']['id'] ?? ''),
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
        return 'Afterpay / Clearpay';
    }
}
