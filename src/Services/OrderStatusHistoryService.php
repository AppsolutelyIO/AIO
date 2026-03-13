<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderStatusHistory;
use Appsolutely\AIO\Repositories\OrderStatusHistoryRepository;
use Appsolutely\AIO\Services\Contracts\OrderStatusHistoryServiceInterface;

final readonly class OrderStatusHistoryService implements OrderStatusHistoryServiceInterface
{
    public function __construct(
        protected OrderStatusHistoryRepository $orderStatusHistoryRepository,
    ) {}

    public function recordStatusChange(
        Order $order,
        ?OrderStatus $fromStatus,
        OrderStatus $toStatus,
        ?int $userId = null,
        ?string $note = null,
    ): OrderStatusHistory {
        return OrderStatusHistory::query()->create([
            'order_id'    => $order->id,
            'from_status' => $fromStatus?->value,
            'to_status'   => $toStatus->value,
            'user_id'     => $userId,
            'note'        => $note,
        ]);
    }
}
