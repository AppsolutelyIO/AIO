<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class CryptoGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $provider = $payment->setting['crypto_provider'] ?? 'manual';

        return match ($provider) {
            'coingate' => $this->chargeCoinGate($order, $payment, $amount, $options),
            default    => $this->chargeManual($order, $payment, $amount),
        };
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        $payment  = $orderPayment->payment;
        $provider = $payment->setting['crypto_provider'] ?? 'manual';

        if ($provider === 'coingate') {
            return $this->refundCoinGate($orderPayment, $amount, $reason);
        }

        return PaymentGatewayResult::failure('Refund not supported for manual crypto payments.');
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $data  = json_decode($payload, true);
        $token = $headers['authorization'] ?? $headers['Authorization'] ?? '';

        // CoinGate sends the API token in the Authorization header for callback verification
        if ($token && $secret && $token !== "Bearer {$secret}" && $token !== $secret) {
            throw new \RuntimeException('Crypto webhook verification failed: invalid token.');
        }

        return [
            'event'            => $data['status'] ?? '',
            'vendor_reference' => (string) ($data['id'] ?? ''),
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
        return 'Cryptocurrency';
    }

    private function chargeCoinGate(Order $order, Payment $payment, int $amount, array $options): PaymentGatewayResult
    {
        $currency = $payment->currency ?? config('appsolutely.currency.code', 'USD');
        $factor   = currency_subunit_factor($currency);

        $baseUrl = $payment->is_test_mode
            ? 'https://api-sandbox.coingate.com/v2'
            : 'https://api.coingate.com/v2';

        try {
            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/orders", [
                    'order_id'         => $order->reference,
                    'price_amount'     => number_format($amount / $factor, 2, '.', ''),
                    'price_currency'   => strtoupper($currency),
                    'receive_currency' => $payment->setting['receive_currency'] ?? strtoupper($currency),
                    'title'            => $order->summary ?? "Order #{$order->reference}",
                    'callback_url'     => $options['notify_url'] ?? config('app.url') . '/payment/crypto/notify',
                    'cancel_url'       => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
                    'success_url'      => $options['success_url'] ?? config('app.url') . '/payment/success',
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['payment_url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['payment_url'],
                    vendorReference: (string) ($data['id'] ?? ''),
                    vendorExtraInfo: [
                        'coingate_order_id' => $data['id'] ?? '',
                        'payment_url'       => $data['payment_url'],
                        'status'            => $data['status'] ?? '',
                    ],
                );
            }

            $errorMsg = $data['message'] ?? $data['reason'] ?? 'CoinGate order creation failed.';

            return PaymentGatewayResult::failure("CoinGate error: {$errorMsg}");
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("CoinGate error: {$e->getMessage()}");
        }
    }

    private function chargeManual(Order $order, Payment $payment, int $amount): PaymentGatewayResult
    {
        return PaymentGatewayResult::pending(
            vendorReference: "CRYPTO-{$order->reference}",
            vendorExtraInfo: [
                'wallet_address' => $payment->setting['wallet_address'] ?? null,
                'network'        => $payment->setting['network'] ?? null,
                'instruction'    => $payment->instruction,
                'amount'         => $amount,
            ],
        );
    }

    private function refundCoinGate(OrderPayment $orderPayment, int $amount, ?string $reason): PaymentGatewayResult
    {
        $payment  = $orderPayment->payment;
        $currency = $payment->currency ?? config('appsolutely.currency.code', 'USD');
        $factor   = currency_subunit_factor($currency);

        $baseUrl = $payment->is_test_mode
            ? 'https://api-sandbox.coingate.com/v2'
            : 'https://api.coingate.com/v2';

        $orderId = $orderPayment->vendor_extra_info['coingate_order_id'] ?? $orderPayment->vendor_reference;

        try {
            $response = Http::withToken($payment->merchant_secret)
                ->post("{$baseUrl}/orders/{$orderId}/refunds", [
                    'amount'   => number_format($amount / $factor, 2, '.', ''),
                    'currency' => strtoupper($currency),
                    'reason'   => $reason ?? '',
                ]);

            $data = $response->json();

            if ($response->successful()) {
                return PaymentGatewayResult::success(
                    vendorReference: (string) ($data['id'] ?? ''),
                    vendorExtraInfo: $data,
                );
            }

            return PaymentGatewayResult::failure($data['message'] ?? 'CoinGate refund failed.');
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("CoinGate refund error: {$e->getMessage()}");
        }
    }
}
