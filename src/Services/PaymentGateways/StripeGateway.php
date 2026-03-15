<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\PaymentGateways;

use Appsolutely\AIO\DTOs\PaymentGatewayResult;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Stripe\StripeClient;
use Stripe\Webhook;

final class StripeGateway extends AbstractGateway
{
    public function charge(Order $order, Payment $payment, int $amount, array $options = []): PaymentGatewayResult
    {
        $currency = $payment->currency ?? config('appsolutely.currency.code', 'USD');

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => strtolower($currency),
                    'unit_amount'  => $amount,
                    'product_data' => [
                        'name' => $order->summary ?? "Order #{$order->reference}",
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => $options['success_url'] ?? config('app.url') . '/payment/success',
            'cancel_url'  => $options['cancel_url'] ?? config('app.url') . '/payment/cancel',
            'metadata'    => [
                'order_id'   => $order->id,
                'order_ref'  => $order->reference,
                'payment_id' => $payment->id,
            ],
        ];

        try {
            $stripe  = new StripeClient($payment->merchant_secret);
            $session = $stripe->checkout->sessions->create($sessionParams);

            return PaymentGatewayResult::redirect(
                redirectUrl: $session->url,
                vendorReference: $session->id,
                vendorExtraInfo: ['session_id' => $session->id, 'payment_intent' => $session->payment_intent],
            );
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Stripe error: {$e->getMessage()}");
        }
    }

    public function refund(OrderPayment $orderPayment, int $amount, ?string $reason = null): PaymentGatewayResult
    {
        try {
            $payment = $orderPayment->payment;
            $stripe  = new StripeClient($payment->merchant_secret);

            $paymentIntent = $orderPayment->vendor_extra_info['payment_intent'] ?? $orderPayment->vendor_reference;

            $refund = $stripe->refunds->create([
                'payment_intent' => $paymentIntent,
                'amount'         => $amount,
                'reason'         => $reason ? 'requested_by_customer' : null,
            ]);

            return PaymentGatewayResult::success(
                vendorReference: $refund->id,
                vendorExtraInfo: ['refund_id' => $refund->id, 'status' => $refund->status],
            );
        } catch (\Throwable $e) {
            return PaymentGatewayResult::failure("Stripe refund error: {$e->getMessage()}");
        }
    }

    public function verifyWebhook(string $payload, array $headers, string $secret): array
    {
        $signature = $headers['stripe-signature'] ?? $headers['Stripe-Signature'] ?? '';

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);

            $object = $event->data->object;

            return [
                'event'            => $event->type,
                'vendor_reference' => $object->id ?? '',
                'status'           => $object->status ?? '',
                'data'             => $object->toArray(),
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException("Stripe webhook verification failed: {$e->getMessage()}");
        }
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
        return 'Stripe';
    }
}
