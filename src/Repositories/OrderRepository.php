<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Order;

final class OrderRepository extends BaseRepository
{
    public function model(): string
    {
        return Order::class;
    }
}
