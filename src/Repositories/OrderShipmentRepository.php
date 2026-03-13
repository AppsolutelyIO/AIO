<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\OrderShipment;

final class OrderShipmentRepository extends BaseRepository
{
    public function model(): string
    {
        return OrderShipment::class;
    }
}
