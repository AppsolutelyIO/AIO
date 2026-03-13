<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Refund;
use Illuminate\Database\Eloquent\Collection;

interface RefundServiceInterface
{
    /**
     * Request a refund for an order payment.
     */
    public function requestRefund(Order $order, OrderPayment $orderPayment, int $amount, string $reason, ?int $userId = null): Refund;

    /**
     * Approve a pending refund.
     */
    public function approveRefund(Refund $refund, ?string $adminNote = null): Refund;

    /**
     * Reject a pending refund.
     */
    public function rejectRefund(Refund $refund, ?string $adminNote = null): Refund;

    /**
     * Mark a refund as completed (money returned).
     */
    public function markAsRefunded(Refund $refund, ?string $vendorReference = null): Refund;

    /**
     * Get all refunds for an order.
     *
     * @return Collection<int, Refund>
     */
    public function getRefundsByOrder(Order $order): Collection;

    /**
     * Calculate the total refundable amount for an order.
     */
    public function getRefundableAmount(Order $order): int;
}
