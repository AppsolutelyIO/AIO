<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class AdyenGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $baseUrl = $payment->is_test_mode
            ? 'https://checkout-test.adyen.com/v71'
            : 'https://checkout-live.adyen.com/v71';

        try {
            $currency        = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'USD'));
            $merchantAccount = $payment->setting['merchant_account'] ?? $payment->merchant_id;

            $sessionParams = [
                'merchantAccount' => $merchantAccount,
                'amount'          => [
                    'value'    => $amount,
                    'currency' => $currency,
                ],
                'reference'   => $order->reference,
                'returnUrl'   => $options['success_url'] ?? config('app.url') . '/payment/success',
                'countryCode' => $payment->setting['country_code'] ?? 'US',
                'metadata'    => [
                    'order_id'   => (string) $order->id,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            $response = Http::withHeaders([
                'X-API-Key'    => $payment->merchant_secret,
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/sessions", $sessionParams);

            $data = $response->json();

            if ($response->successful() && isset($data['sessionData'])) {
                $redirectUrl = $data['url'] ?? $options['success_url'] ?? config('app.url') . '/payment/adyen/checkout';

                return PaymentGatewayResult::redirect(
                    redirectUrl: $redirectUrl,
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'session_id'   => $data['id'] ?? '',
                        'session_data' => $data['sessionData'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['message'] ?? $data['errorType'] ?? 'Adyen session creation failed.';

            return PaymentGatewayResult::failure("Adyen error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Adyen error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $baseUrl = $payment->is_test_mode
                ? 'https://checkout-test.adyen.com/v71'
                : 'https://checkout-live.adyen.com/v71';

            $pspReference    = $orderPayment->vendor_extra_info['psp_reference'] ?? $orderPayment->vendor_reference;
            $merchantAccount = $payment->setting['merchant_account'] ?? $payment->merchant_id;
            $currency        = strtoupper($payment->currency ?? 'USD');

            $response = Http::withHeaders([
                'X-API-Key'    => $payment->merchant_secret,
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/payments/{$pspReference}/refunds", [
                'merchantAccount' => $merchantAccount,
                'amount'          => [
                    'value'    => $amount,
                    'currency' => $currency,
                ],
                'reference' => $reason ?? 'Refund requested',
            ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['pspReference'] ?? '',
                    vendorExtraInfo: ['psp_reference' => $data['pspReference'] ?? '', 'status' => $data['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Adyen refund error: ' . ($data['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Adyen refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $data = json_decode($payload, true);
        $item = $data['notificationItems'][0]['NotificationRequestItem'] ?? [];

        // Adyen payment webhooks embed HMAC in additionalData.hmacSignature.
        // The signature is computed from 8 specific fields concatenated with colons.
        // See: https://docs.adyen.com/development-resources/webhooks/secure-webhooks/verify-hmac-signatures
        $signature = $item['additionalData']['hmacSignature'] ?? '';

        if (empty($signature)) {
            throw new \RuntimeException('Adyen webhook signature verification failed.');
        }

        $signPayload = implode(':', [
            $item['pspReference'] ?? '',
            $item['originalReference'] ?? '',
            $item['merchantAccountCode'] ?? '',
            $item['merchantReference'] ?? '',
            $item['amount']['value'] ?? '',
            $item['amount']['currency'] ?? '',
            $item['eventCode'] ?? '',
            $item['success'] ?? '',
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $signPayload, hex2bin($secret), true));

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Adyen webhook signature verification failed.');
        }

        return [
            'event'            => $item['eventCode'] ?? '',
            'vendor_reference' => $item['pspReference'] ?? '',
            'status'           => ($item['success'] ?? '') === 'true' ? 'success' : 'failed',
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
        return 'Adyen';
    }
}
