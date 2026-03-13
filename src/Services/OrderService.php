<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Events\OrderCancelled;
use Appsolutely\AIO\Events\OrderCompleted;
use Appsolutely\AIO\Events\OrderPaid;
use Appsolutely\AIO\Events\OrderShipped;
use Appsolutely\AIO\Events\OrderStatusUpdated;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Repositories\OrderItemRepository;
use Appsolutely\AIO\Repositories\OrderPaymentRepository;
use Appsolutely\AIO\Repositories\OrderRepository;
use Appsolutely\AIO\Repositories\OrderShipmentRepository;
use Appsolutely\AIO\Services\Contracts\OrderServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class OrderService implements OrderServiceInterface
{
    private const array ALLOWED_TRANSITIONS = [
        'pending'   => ['paid', 'cancelled'],
        'paid'      => ['shipped', 'cancelled'],
        'shipped'   => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository,
        protected OrderPaymentRepository $orderPaymentRepository,
        protected OrderShipmentRepository $orderShipmentRepository,
    ) {}

    public function findById(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function findByReference(string $reference): ?Order
    {
        return Order::query()->where('reference', $reference)->first();
    }

    public function getOrdersByUser(int $userId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        if (! $this->canTransitionTo($order, $status)) {
            throw new \InvalidArgumentException(
                "Cannot transition order from {$order->status->value} to {$status->value}."
            );
        }

        $order->update(['status' => $status]);

        $order = $order->fresh();
        OrderStatusUpdated::dispatch($order);

        return $order;
    }

    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if (! $this->canTransitionTo($order, OrderStatus::Cancelled)) {
            throw new \InvalidArgumentException(
                "Cannot cancel order with status {$order->status->value}."
            );
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'remark' => $reason ?? $order->remark,
        ]);

        $order = $order->fresh();
        OrderCancelled::dispatch($order);

        return $order;
    }

    public function markAsPaid(Order $order): Order
    {
        $order = $this->updateStatus($order, OrderStatus::Paid);

        OrderPaid::dispatch($order);

        return $order;
    }

    public function markAsShipped(Order $order): Order
    {
        $order = $this->updateStatus($order, OrderStatus::Shipped);

        OrderShipped::dispatch($order);

        return $order;
    }

    public function markAsCompleted(Order $order): Order
    {
        $order = $this->updateStatus($order, OrderStatus::Completed);

        OrderCompleted::dispatch($order);

        return $order;
    }

    public function canTransitionTo(Order $order, OrderStatus $status): bool
    {
        $currentStatus = $order->status->value;
        $allowed       = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        return in_array($status->value, $allowed, true);
    }
}
