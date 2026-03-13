<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class RazorpayGateway extends AbstractGateway
{
    private const API_URL = 'https://api.razorpay.com/v1';

    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        try {
            $currency = strtoupper($payment->currency ?? config('appsolutely.currency.code', 'INR'));

            $paymentLinkParams = [
                'amount'          => $amount,
                'currency'        => $currency,
                'description'     => $order->summary ?? "Order #{$order->reference}",
                'reference_id'    => $order->reference,
                'callback_url'    => $options['success_url'] ?? config('app.url') . '/payment/success',
                'callback_method' => 'get',
                'notes'           => [
                    'order_id'   => (string) $order->id,
                    'payment_id' => (string) $payment->id,
                ],
            ];

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post(self::API_URL . '/payment_links', $paymentLinkParams);

            $data = $response->json();

            if ($response->successful() && isset($data['short_url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['short_url'],
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: [
                        'payment_link_id' => $data['id'] ?? '',
                        'short_url'       => $data['short_url'],
                    ],
                );
            }

            $errorMsg = $data['error']['description'] ?? 'Razorpay payment link creation failed.';

            return PaymentGatewayResult::failure("Razorpay error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Razorpay error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment   = $orderPayment->payment;
            $paymentId = $orderPayment->vendor_extra_info['razorpay_payment_id'] ?? $orderPayment->vendor_reference;

            $response = Http::withBasicAuth($payment->merchant_id, $payment->merchant_secret)
                ->post(self::API_URL . "/payments/{$paymentId}/refund", [
                    'amount' => $amount,
                    'notes'  => ['reason' => $reason ?? 'Refund requested'],
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: $data['id'] ?? '',
                    vendorExtraInfo: ['refund_id' => $data['id'] ?? '', 'status' => $data['status'] ?? ''],
                );
            }

            return PaymentGatewayResult::failure('Razorpay refund error: ' . ($data['error']['description'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Razorpay refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['x-razorpay-signature'] ?? $headers['X-Razorpay-Signature'] ?? '';

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expectedSignature, $signature)) {
            throw new \RuntimeException('Razorpay webhook signature verification failed.');
        }

        $data = json_decode($payload, true);

        return [
            'event'            => $data['event'] ?? '',
            'vendor_reference' => $data['payload']['payment']['entity']['id'] ?? '',
            'status'           => $data['payload']['payment']['entity']['status'] ?? '',
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
        return 'Razorpay';
    }
}
