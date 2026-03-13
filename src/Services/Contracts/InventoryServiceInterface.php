<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\InventoryMovementType;
use Appsolutely\AIO\Models\InventoryMovement;
use Appsolutely\AIO\Models\ProductSku;
use Illuminate\Database\Eloquent\Model;

interface InventoryServiceInterface
{
    /**
     * Record an inventory movement and update SKU stock.
     */
    public function recordMovement(
        ProductSku $sku,
        InventoryMovementType $type,
        int $quantity,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $reason = null,
    ): InventoryMovement;

    /**
     * Check if the SKU has sufficient stock for the given quantity.
     */
    public function hasStock(ProductSku $sku, int $quantity): bool;
}
