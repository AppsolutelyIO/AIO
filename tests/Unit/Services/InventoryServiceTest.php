<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\InventoryMovementType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InventoryService::class);
    }

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

    public function test_record_movement_deducts_stock_on_sale(): void
    {
        $sku = $this->createSku(100);

        $movement = $this->service->recordMovement(
            $sku,
            InventoryMovementType::Sale,
            -5,
            reason: 'Order #123'
        );

        $sku->refresh();
        $this->assertEquals(95, $sku->stock);
        $this->assertEquals(100, $movement->stock_before);
        $this->assertEquals(95, $movement->stock_after);
        $this->assertEquals(-5, $movement->quantity);
        $this->assertEquals(InventoryMovementType::Sale, $movement->type);
    }

    public function test_record_movement_adds_stock_on_purchase(): void
    {
        $sku = $this->createSku(50);

        $movement = $this->service->recordMovement(
            $sku,
            InventoryMovementType::Purchase,
            30,
            reason: 'Restock'
        );

        $sku->refresh();
        $this->assertEquals(80, $sku->stock);
        $this->assertEquals(50, $movement->stock_before);
        $this->assertEquals(80, $movement->stock_after);
    }

    public function test_record_movement_adds_stock_on_return(): void
    {
        $sku = $this->createSku(90);

        $movement = $this->service->recordMovement(
            $sku,
            InventoryMovementType::Return,
            5,
            reason: 'Customer return'
        );

        $sku->refresh();
        $this->assertEquals(95, $sku->stock);
        $this->assertEquals(InventoryMovementType::Return, $movement->type);
    }

    public function test_record_movement_with_reference_model(): void
    {
        $sku   = $this->createSku(100);
        $order = \App\Models\Order::factory()->create();

        $movement = $this->service->recordMovement(
            $sku,
            InventoryMovementType::Sale,
            -1,
            reference: $order,
        );

        $this->assertEquals($order->getMorphClass(), $movement->reference_type);
        $this->assertEquals($order->id, $movement->reference_id);
    }

    public function test_has_stock_returns_true_when_sufficient(): void
    {
        $sku = $this->createSku(10);

        $this->assertTrue($this->service->hasStock($sku, 5));
        $this->assertTrue($this->service->hasStock($sku, 10));
    }

    public function test_has_stock_returns_false_when_insufficient(): void
    {
        $sku = $this->createSku(5);

        $this->assertFalse($this->service->hasStock($sku, 6));
    }
}
