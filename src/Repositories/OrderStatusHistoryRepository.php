<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\OrderStatusHistory;

final class OrderStatusHistoryRepository extends BaseRepository
{
    public function model(): string
    {
        return OrderStatusHistory::class;
    }
}
