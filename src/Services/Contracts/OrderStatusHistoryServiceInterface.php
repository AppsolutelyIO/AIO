<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderStatusHistory;

interface OrderStatusHistoryServiceInterface
{
    /**
     * Record an order status change.
     */
    public function recordStatusChange(
        Order $order,
        ?OrderStatus $fromStatus,
        OrderStatus $toStatus,
        ?int $userId = null,
        ?string $note = null,
    ): OrderStatusHistory;
}
