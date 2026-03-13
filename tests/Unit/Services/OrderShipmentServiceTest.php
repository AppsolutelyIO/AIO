<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Services\OrderShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class OrderShipmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderShipmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderShipmentService::class);
    }

    public function test_create_shipment(): void
    {
        $order = Order::factory()->create();

        $shipment = $this->service->createShipment($order, [
            'name'    => 'John Doe',
            'address' => '123 Main St',
            'city'    => 'New York',
            'country' => 'US',
        ]);

        $this->assertInstanceOf(OrderShipment::class, $shipment);
        $this->assertEquals($order->id, $shipment->order_id);
        $this->assertEquals(OrderShipmentStatus::Pending, $shipment->status);
        $this->assertEquals('John Doe', $shipment->name);
    }

    public function test_mark_as_shipped(): void
    {
        $shipment = OrderShipment::factory()->create();

        $updated = $this->service->markAsShipped($shipment, 'ups', 'TRACK123');

        $this->assertEquals(OrderShipmentStatus::Shipped, $updated->status);
        $this->assertEquals('ups', $updated->delivery_vendor);
        $this->assertEquals('TRACK123', $updated->delivery_reference);
    }

    public function test_mark_as_delivered(): void
    {
        $shipment = OrderShipment::factory()->shipped()->create();

        $updated = $this->service->markAsDelivered($shipment);

        $this->assertEquals(OrderShipmentStatus::Delivered, $updated->status);
    }

    public function test_get_shipments_by_order(): void
    {
        $order = Order::factory()->create();
        OrderShipment::factory()->count(2)->create(['order_id' => $order->id]);

        $shipments = $this->service->getShipmentsByOrder($order);

        $this->assertCount(2, $shipments);
    }

    public function test_is_order_fully_shipped_returns_true(): void
    {
        $order = Order::factory()->create();
        OrderShipment::factory()->delivered()->count(2)->create(['order_id' => $order->id]);

        $this->assertTrue($this->service->isOrderFullyShipped($order));
    }

    public function test_is_order_fully_shipped_returns_false_when_pending(): void
    {
        $order = Order::factory()->create();
        OrderShipment::factory()->delivered()->create(['order_id' => $order->id]);
        OrderShipment::factory()->create(['order_id' => $order->id]); // pending

        $this->assertFalse($this->service->isOrderFullyShipped($order));
    }

    public function test_is_order_fully_shipped_returns_false_when_no_shipments(): void
    {
        $order = Order::factory()->create();

        $this->assertFalse($this->service->isOrderFullyShipped($order));
    }

    public function test_update_status(): void
    {
        $shipment = OrderShipment::factory()->create();

        $updated = $this->service->updateStatus($shipment, OrderShipmentStatus::Shipped);

        $this->assertEquals(OrderShipmentStatus::Shipped, $updated->status);
    }
}
