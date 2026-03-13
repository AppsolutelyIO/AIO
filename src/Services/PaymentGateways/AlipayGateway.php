<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;

final class AlipayGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $currency = $payment->currency ?? 'CNY';
        $factor   = currency_subunit_factor($currency);

        $params = [
            'app_id'      => $payment->merchant_id,
            'method'      => 'alipay.trade.page.pay',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => now()->format('Y-m-d H:i:s'),
            'version'     => '1.0',
            'notify_url'  => $options['notify_url'] ?? config('app.url') . '/payment/alipay/notify',
            'return_url'  => $options['success_url'] ?? config('app.url') . '/payment/success',
            'biz_content' => json_encode([
                'out_trade_no' => $order->reference,
                'total_amount' => number_format($amount / $factor, 2, '.', ''),
                'subject'      => $order->summary ?? "Order #{$order->reference}",
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
            ]),
        ];

        $params['sign'] = $this->generateSign($params, $payment->merchant_key);

        $baseUrl = $payment->is_test_mode
            ? 'https://openapi-sandbox.dl.alipaydev.com/gateway.do'
            : 'https://openapi.alipay.com/gateway.do';

        $redirectUrl = $baseUrl . '?' . http_build_query($params);

        return PaymentGatewayResult::redirect(
            redirectUrl: $redirectUrl,
            vendorReference: $order->reference,
            vendorExtraInfo: ['trade_no' => $order->reference],
        );
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        $payment  = $orderPayment->payment;
        $currency = $payment->currency ?? 'CNY';
        $factor   = currency_subunit_factor($currency);

        $params = [
            'app_id'      => $payment->merchant_id,
            'method'      => 'alipay.trade.refund',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => now()->format('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode([
                'trade_no'      => $orderPayment->vendor_reference,
                'refund_amount' => number_format($amount / $factor, 2, '.', ''),
                'refund_reason' => $reason ?? '',
            ]),
        ];

        $params['sign'] = $this->generateSign($params, $payment->merchant_key);

        return PaymentGatewayResult::pending(
            vendorReference: $orderPayment->vendor_reference,
            vendorExtraInfo: ['refund_params' => $params],
        );
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        parse_str($payload, $data);

        $sign     = $data['sign'] ?? '';
        $signType = $data['sign_type'] ?? 'RSA2';

        // Remove sign and sign_type before building the verification string
        $verifyData = $data;
        unset($verifyData['sign'], $verifyData['sign_type']);
        ksort($verifyData);

        $signContent = urldecode(http_build_query($verifyData));

        // Verify signature using Alipay's public key (passed as $secret)
        $publicKey = openssl_pkey_get_public($secret);
        if ($publicKey === false) {
            throw new \RuntimeException('Alipay webhook verification failed: invalid public key.');
        }

        $algorithm = $signType === 'RSA' ? OPENSSL_ALGO_SHA1 : OPENSSL_ALGO_SHA256;
        $result    = openssl_verify($signContent, base64_decode($sign), $publicKey, $algorithm);

        if ($result !== 1) {
            throw new \RuntimeException('Alipay webhook signature verification failed.');
        }

        return [
            'event'            => $data['trade_status'] ?? '',
            'vendor_reference' => $data['trade_no'] ?? '',
            'status'           => $data['trade_status'] ?? '',
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
        return 'Alipay';
    }

    private function generateSign(array $params, string $privateKey): string
    {
        ksort($params);
        $signContent = urldecode(http_build_query($params));

        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            return '';
        }

        openssl_sign($signContent, $signature, $key, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }
}
