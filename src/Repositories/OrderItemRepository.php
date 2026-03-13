<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\OrderItem;

final class OrderItemRepository extends BaseRepository
{
    public function model(): string
    {
        return OrderItem::class;
    }
}
