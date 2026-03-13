<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class PaypalGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $currency = $payment->currency ?? config('appsolutely.currency.code', 'USD');
        $factor   = currency_subunit_factor($currency);

        $baseUrl = $payment->is_test_mode
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        try {
            $tokenResponse = Http::asForm()
                ->withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            $accessToken = $tokenResponse->json('access_token');

            if (! $accessToken) {
                return PaymentGatewayResult::failure('PayPal authentication failed.');
            }

            $orderResponse = Http::withToken($accessToken)
                ->post("{$baseUrl}/v2/checkout/orders", [
                    'intent'         => 'CAPTURE',
                    'purchase_units' => [[
                        'reference_id' => $order->reference,
                        'amount'       => [
                            'currency_code' => strtoupper($currency),
                            'value'         => number_format($amount / $factor, 2, '.', ''),
                        ],
                    ]],
                    'application_context' => [
                        'return_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
                        'cancel_url' => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
                    ],
                ]);

            $orderData = $orderResponse->json();

            $approveLink = collect($orderData['links'] ?? [])
                ->firstWhere('rel', 'approve');

            return PaymentGatewayResult::redirect(
                redirectUrl: $approveLink['href'] ?? '',
                vendorReference: $orderData['id'] ?? '',
                vendorExtraInfo: ['paypal_order_id' => $orderData['id'] ?? ''],
            );
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("PayPal error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment  = $orderPayment->payment;
            $currency = $payment->currency ?? config('appsolutely.currency.code', 'USD');
            $factor   = currency_subunit_factor($currency);

            $baseUrl = $payment->is_test_mode
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com';

            $tokenResponse = Http::asForm()
                ->withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post("{$baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            $accessToken = $tokenResponse->json('access_token');

            if (! $accessToken) {
                return PaymentGatewayResult::failure('PayPal authentication failed.');
            }

            $captureId = $orderPayment->vendor_extra_info['capture_id'] ?? $orderPayment->vendor_reference;

            $refundResponse = Http::withToken($accessToken)
                ->post("{$baseUrl}/v2/payments/captures/{$captureId}/refund", [
                    'amount' => [
                        'value'         => number_format($amount / $factor, 2, '.', ''),
                        'currency_code' => strtoupper($currency),
                    ],
                    'note_to_payer' => $reason,
                ]);

            $refundData = $refundResponse->json();

            return PaymentGatewayResult::success(
                vendorReference: $refundData['id'] ?? '',
                vendorExtraInfo: $refundData,
            );
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("PayPal refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $data = json_decode($payload, true);

        return [
            'event'            => $data['event_type'] ?? '',
            'vendor_reference' => $data['resource']['id'] ?? '',
            'status'           => $data['resource']['status'] ?? '',
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
        return 'PayPal';
    }
}
