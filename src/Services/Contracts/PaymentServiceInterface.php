<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\PaymentProvider;
use Appsolutely\AIO\Models\Payment;
use Appsolutely\AIO\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface PaymentServiceInterface
{
    /**
     * Get all payments.
     */
    public function getPayments(): Collection;

    /**
     * Get all active payments.
     */
    public function getActivePayments(): Collection;

    /**
     * Get payments by provider.
     */
    public function getPaymentsByProvider(PaymentProvider $provider): Collection;

    /**
     * Get available payments for a specific product.
     * Uses the payment_methods JSON field. Falls back to all active payments if none assigned.
     */
    public function getAvailablePaymentsForProduct(Product $product): Collection;

    /**
     * Find a payment by its ID.
     */
    public function findPayment(int $id): ?Payment;

    /**
     * Create a new payment configuration.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPayment(array $data): Payment;

    /**
     * Update an existing payment configuration.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePayment(Payment $payment, array $data): Payment;
}
