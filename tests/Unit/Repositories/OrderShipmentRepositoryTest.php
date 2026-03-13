<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Repositories\OrderShipmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class OrderShipmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderShipmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(OrderShipmentRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(OrderShipmentRepository::class, $this->repository);
    }

    public function test_model_returns_order_shipment_class(): void
    {
        $this->assertEquals(OrderShipment::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_order_shipment(): void
    {
        $order = Order::factory()->create();

        $shipment = $this->repository->create([
            'order_id'     => $order->id,
            'product_type' => 'physical',
            'email'        => 'ship@example.com',
            'name'         => 'Test Recipient',
            'address'      => '123 Test St',
            'city'         => 'Sydney',
            'postcode'     => '2000',
            'country'      => 'AU',
            'status'       => 'pending',
        ]);

        $this->assertInstanceOf(OrderShipment::class, $shipment);
        $this->assertDatabaseHas('order_shipments', ['order_id' => $order->id]);
    }

    public function test_find_by_field_returns_shipments_for_order(): void
    {
        $order = Order::factory()->create();

        OrderShipment::create([
            'order_id'     => $order->id,
            'product_type' => 'physical',
            'email'        => 'find@example.com',
            'name'         => 'Find Recipient',
            'address'      => '456 Find Ave',
            'city'         => 'Melbourne',
            'postcode'     => '3000',
            'country'      => 'AU',
            'status'       => 'pending',
        ]);

        $result = $this->repository->findByField('order_id', $order->id);

        $this->assertCount(1, $result);
        $this->assertEquals($order->id, $result->first()->order_id);
    }
}
