<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\InventoryMovementType;
use Appsolutely\AIO\Models\InventoryMovement;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Repositories\InventoryMovementRepository;
use Appsolutely\AIO\Services\Contracts\InventoryServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        protected InventoryMovementRepository $inventoryMovementRepository,
    ) {}

    public function recordMovement(
        ProductSku $sku,
        InventoryMovementType $type,
        int $quantity,
        ?Model $reference = null,
        ?int $userId = null,
        ?string $reason = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($sku, $type, $quantity, $reference, $userId, $reason) {
            $sku->lockForUpdate();
            $sku->refresh();

            $stockBefore = $sku->stock;
            $stockAfter  = $stockBefore + $quantity;

            $sku->update(['stock' => $stockAfter]);

            return InventoryMovement::query()->create([
                'product_sku_id' => $sku->id,
                'type'           => $type,
                'quantity'       => $quantity,
                'stock_before'   => $stockBefore,
                'stock_after'    => $stockAfter,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id'   => $reference?->getKey(),
                'user_id'        => $userId,
                'reason'         => $reason,
            ]);
        });
    }

    public function hasStock(ProductSku $sku, int $quantity): bool
    {
        return $sku->stock >= $quantity;
    }
}
