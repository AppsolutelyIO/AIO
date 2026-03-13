<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Events\RefundProcessed;
use Appsolutely\AIO\Events\RefundRequested;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Repositories\RefundRepository;
use Appsolutely\AIO\Services\Contracts\RefundServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final readonly class RefundService implements RefundServiceInterface
{
    public function __construct(
        protected RefundRepository $refundRepository,
    ) {}

    public function requestRefund(Order $order, OrderPayment $orderPayment, int $amount, string $reason, ?int $userId = null): Refund
    {
        $refundableAmount = $this->getRefundableAmount($order);

        if ($amount > $refundableAmount) {
            throw new \InvalidArgumentException(
                "Refund amount ({$amount}) exceeds refundable amount ({$refundableAmount})."
            );
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Refund amount must be positive.');
        }

        $refund = Refund::query()->create([
            'reference'        => 'RF-' . strtoupper(Str::random(12)),
            'order_id'         => $order->id,
            'order_payment_id' => $orderPayment->id,
            'user_id'          => $userId ?? $order->user_id,
            'amount'           => $amount,
            'status'           => RefundStatus::Pending,
            'reason'           => $reason,
        ]);

        RefundRequested::dispatch($refund);

        return $refund;
    }

    public function approveRefund(Refund $refund, ?string $adminNote = null): Refund
    {
        if ($refund->status !== RefundStatus::Pending) {
            throw new \InvalidArgumentException(
                "Cannot approve refund with status {$refund->status->value}."
            );
        }

        $refund->update([
            'status'     => RefundStatus::Approved,
            'admin_note' => $adminNote,
        ]);

        $refund = $refund->fresh();
        RefundProcessed::dispatch($refund);

        return $refund;
    }

    public function rejectRefund(Refund $refund, ?string $adminNote = null): Refund
    {
        if ($refund->status !== RefundStatus::Pending) {
            throw new \InvalidArgumentException(
                "Cannot reject refund with status {$refund->status->value}."
            );
        }

        $refund->update([
            'status'     => RefundStatus::Rejected,
            'admin_note' => $adminNote,
        ]);

        $refund = $refund->fresh();
        RefundProcessed::dispatch($refund);

        return $refund;
    }

    public function markAsRefunded(Refund $refund, ?string $vendorReference = null): Refund
    {
        if ($refund->status !== RefundStatus::Approved) {
            throw new \InvalidArgumentException(
                "Cannot mark as refunded. Refund must be approved first. Current status: {$refund->status->value}."
            );
        }

        $refund->update([
            'status'           => RefundStatus::Refunded,
            'vendor_reference' => $vendorReference,
            'refunded_at'      => now(),
        ]);

        $refund = $refund->fresh();
        RefundProcessed::dispatch($refund);

        return $refund;
    }

    public function getRefundsByOrder(Order $order): Collection
    {
        return $order->refunds()->orderByDesc('created_at')->get();
    }

    public function getRefundableAmount(Order $order): int
    {
        $totalAmount   = (int) $order->total_amount;
        $refundedTotal = (int) $order->refunds()
            ->whereIn('status', [RefundStatus::Pending, RefundStatus::Approved, RefundStatus::Refunded])
            ->sum('amount');

        return max(0, $totalAmount - $refundedTotal);
    }
}
