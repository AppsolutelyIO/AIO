<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderServiceInterface
{
    /**
     * Find an order by its ID.
     */
    public function findById(int $id): ?Order;

    /**
     * Find an order by its reference.
     */
    public function findByReference(string $reference): ?Order;

    /**
     * Get all orders for a given user.
     *
     * @return Collection<int, Order>
     */
    public function getOrdersByUser(int $userId): Collection;

    /**
     * Update the status of an order.
     */
    public function updateStatus(Order $order, OrderStatus $status): Order;

    /**
     * Cancel an order (only if still pending).
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order;

    /**
     * Mark an order as paid.
     */
    public function markAsPaid(Order $order): Order;

    /**
     * Mark an order as shipped.
     */
    public function markAsShipped(Order $order): Order;

    /**
     * Mark an order as completed.
     */
    public function markAsCompleted(Order $order): Order;

    /**
     * Check if an order can transition to the given status.
     */
    public function canTransitionTo(Order $order, OrderStatus $status): bool;
}
