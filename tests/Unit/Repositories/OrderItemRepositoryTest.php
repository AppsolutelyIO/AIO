<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Repositories\OrderItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class OrderItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderItemRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(OrderItemRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(OrderItemRepository::class, $this->repository);
    }

    public function test_model_returns_order_item_class(): void
    {
        $this->assertEquals(OrderItem::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_order_item(): void
    {
        $order   = Order::factory()->create();
        $product = Product::factory()->create();

        $item = $this->repository->create([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'product_sku_id' => $product->id,
            'reference'      => 'ITEM-001',
            'summary'        => 'Test item',
            'original_price' => 5000,
            'price'          => 5000,
            'quantity'       => 1,
            'amount'         => 5000,
        ]);

        $this->assertInstanceOf(OrderItem::class, $item);
        $this->assertDatabaseHas('order_items', ['reference' => 'ITEM-001']);
    }

    public function test_find_by_field_returns_items_for_order(): void
    {
        $order   = Order::factory()->create();
        $product = Product::factory()->create();

        DB::table('order_items')->insert([
            'order_id'       => $order->id,
            'product_id'     => $product->id,
            'product_sku_id' => $product->id,
            'reference'      => 'ITEM-A',
            'summary'        => 'A',
            'original_price' => 1000,
            'price'          => 1000,
            'quantity'       => 1,
            'amount'         => 1000,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $result = $this->repository->findByField('order_id', $order->id);

        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result->first()->order_id);
    }
}
