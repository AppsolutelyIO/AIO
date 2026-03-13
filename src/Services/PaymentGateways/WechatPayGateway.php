<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Support\Facades\Http;

final class WechatPayGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $currency = $payment->currency ?? 'CNY';

        $baseUrl = 'https://api.mch.weixin.qq.com/v3';

        $body = [
            'appid'        => $payment->setting['app_id'] ?? $payment->merchant_id,
            'mchid'        => $payment->merchant_id,
            'description'  => $order->summary ?? "Order #{$order->reference}",
            'out_trade_no' => $order->reference,
            'notify_url'   => $options['notify_url'] ?? config('app.url') . '/payment/wechat/notify',
            'amount'       => [
                'total'    => $amount,
                'currency' => strtoupper($currency),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->buildAuthorization($payment, 'POST', '/v3/pay/transactions/native', $body),
            ])->post("{$baseUrl}/pay/transactions/native", $body);

            $data = $response->json();

            if (isset($data['code_url'])) {
                return PaymentGatewayResult::redirect(
                    redirectUrl: $data['code_url'],
                    vendorReference: $order->reference,
                    vendorExtraInfo: ['code_url' => $data['code_url']],
                );
            }

            return PaymentGatewayResult::failure($data['message'] ?? 'WeChat Pay request failed.');
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("WeChat Pay error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        $payment  = $orderPayment->payment;
        $currency = $payment->currency ?? 'CNY';

        $body = [
            'transaction_id' => $orderPayment->vendor_reference,
            'out_refund_no'  => 'RF-' . $orderPayment->reference,
            'reason'         => $reason ?? '',
            'amount'         => [
                'refund'   => $amount,
                'total'    => $orderPayment->payment_amount,
                'currency' => strtoupper($currency),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->buildAuthorization($payment, 'POST', '/v3/refund/domestic/refunds', $body),
            ])->post('https://api.mch.weixin.qq.com/v3/refund/domestic/refunds', $body);

            $data = $response->json();

            return PaymentGatewayResult::success(
                vendorReference: $data['refund_id'] ?? '',
                vendorExtraInfo: $data,
            );
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("WeChat Pay refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $data = json_decode($payload, true);

        $resource  = $data['resource'] ?? [];
        $decrypted = [];

        if (isset($resource['ciphertext'], $resource['nonce'], $resource['associated_data'])) {
            $ciphertext     = base64_decode($resource['ciphertext']);
            $nonce          = $resource['nonce'];
            $associatedData = $resource['associated_data'];

            $decryptedJson = openssl_decrypt(
                substr($ciphertext, 0, -16),
                'aes-256-gcm',
                $secret,
                OPENSSL_RAW_DATA,
                $nonce,
                substr($ciphertext, -16),
                $associatedData,
            );

            if ($decryptedJson !== false) {
                $decrypted = json_decode($decryptedJson, true) ?? [];
            }
        }

        return [
            'event'            => $data['event_type'] ?? '',
            'vendor_reference' => $decrypted['transaction_id'] ?? $decrypted['out_trade_no'] ?? '',
            'status'           => $decrypted['trade_state'] ?? ($data['event_type'] ?? ''),
            'data'             => $decrypted ?: $data,
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
        return 'WeChat Pay';
    }

    private function buildAuthorization(Payment $payment, string $method, string $path, array $body): string
    {
        $timestamp = time();
        $nonce     = bin2hex(random_bytes(16));
        $bodyJson  = json_encode($body);

        $message = "{$method}\n{$path}\n{$timestamp}\n{$nonce}\n{$bodyJson}\n";

        $key = openssl_pkey_get_private($payment->merchant_key);
        if ($key === false) {
            return '';
        }

        openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256);
        $signBase64 = base64_encode($signature);
        $serialNo   = $payment->setting['serial_no'] ?? '';

        return "WECHATPAY2-SHA256-RSA2048 mchid=\"{$payment->merchant_id}\",serial_no=\"{$serialNo}\",nonce_str=\"{$nonce}\",timestamp=\"{$timestamp}\",signature=\"{$signBase64}\"";
    }
}
