<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\InventoryMovementType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\InventoryMovement;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    private function createSku(int $stock = 100): ProductSku
    {
        $product = Product::factory()->create();
        $id      = DB::table('product_skus')->insertGetId([
            'product_id' => $product->id,
            'slug'       => 'sku-' . uniqid(),
            'title'      => 'Test SKU',
            'price'      => 1000,
            'stock'      => $stock,
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return ProductSku::find($id);
    }

    public function test_inventory_movement_can_be_created(): void
    {
        $sku = $this->createSku(100);

        $movement = InventoryMovement::create([
            'product_sku_id' => $sku->id,
            'type'           => InventoryMovementType::Sale,
            'quantity'       => -5,
            'stock_before'   => 100,
            'stock_after'    => 95,
            'reason'         => 'Order placed',
        ]);

        $this->assertDatabaseHas('inventory_movements', ['id' => $movement->id]);
        $this->assertEquals(InventoryMovementType::Sale, $movement->type);
        $this->assertEquals(-5, $movement->quantity);
    }

    public function test_inventory_movement_belongs_to_product_sku(): void
    {
        $sku = $this->createSku();

        $movement = InventoryMovement::create([
            'product_sku_id' => $sku->id,
            'type'           => InventoryMovementType::Purchase,
            'quantity'       => 50,
            'stock_before'   => 100,
            'stock_after'    => 150,
        ]);

        $this->assertInstanceOf(ProductSku::class, $movement->productSku);
        $this->assertEquals($sku->id, $movement->productSku->id);
    }

    public function test_product_sku_has_many_inventory_movements(): void
    {
        $sku = $this->createSku(100);

        InventoryMovement::create([
            'product_sku_id' => $sku->id,
            'type'           => InventoryMovementType::Purchase,
            'quantity'       => 50,
            'stock_before'   => 100,
            'stock_after'    => 150,
        ]);

        InventoryMovement::create([
            'product_sku_id' => $sku->id,
            'type'           => InventoryMovementType::Sale,
            'quantity'       => -10,
            'stock_before'   => 150,
            'stock_after'    => 140,
        ]);

        $this->assertCount(2, $sku->inventoryMovements);
    }

    public function test_inventory_movement_has_morph_reference(): void
    {
        $sku   = $this->createSku();
        $order = \App\Models\Order::factory()->create();

        $movement = InventoryMovement::create([
            'product_sku_id' => $sku->id,
            'type'           => InventoryMovementType::Sale,
            'quantity'       => -1,
            'stock_before'   => 100,
            'stock_after'    => 99,
            'reference_type' => $order->getMorphClass(),
            'reference_id'   => $order->id,
        ]);

        $this->assertInstanceOf(\App\Models\Order::class, $movement->reference);
    }
}
