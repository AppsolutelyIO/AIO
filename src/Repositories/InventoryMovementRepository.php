<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\InventoryMovement;

final class InventoryMovementRepository extends BaseRepository
{
    public function model(): string
    {
        return InventoryMovement::class;
    }
}
