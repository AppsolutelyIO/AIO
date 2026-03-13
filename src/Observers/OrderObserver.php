<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Services\Contracts\OrderStatusHistoryServiceInterface;

final class OrderObserver
{
    public function __construct(
        protected OrderStatusHistoryServiceInterface $orderStatusHistoryService,
    ) {}

    public function created(Order $order): void
    {
        if ($order->status instanceof OrderStatus) {
            $this->orderStatusHistoryService->recordStatusChange(
                $order,
                null,
                $order->status,
            );
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status instanceof OrderStatus) {
            $original       = $order->getOriginal('status');
            $originalStatus = $original instanceof OrderStatus ? $original : OrderStatus::tryFrom((string) $original);

            $this->orderStatusHistoryService->recordStatusChange(
                $order,
                $originalStatus,
                $order->status,
            );
        }
    }
}
