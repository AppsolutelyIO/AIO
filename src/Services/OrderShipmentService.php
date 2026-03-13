<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Repositories\OrderShipmentRepository;
use Appsolutely\AIO\Services\Contracts\OrderShipmentServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class OrderShipmentService implements OrderShipmentServiceInterface
{
    public function __construct(protected OrderShipmentRepository $orderShipmentRepository) {}

    public function createShipment(Order $order, array $shippingData): OrderShipment
    {
        return OrderShipment::query()->create(array_merge(
            ['order_id' => $order->id, 'status' => OrderShipmentStatus::Pending],
            $shippingData,
        ));
    }

    public function updateStatus(OrderShipment $shipment, OrderShipmentStatus $status): OrderShipment
    {
        $shipment->update(['status' => $status]);

        return $shipment->fresh();
    }

    public function markAsShipped(OrderShipment $shipment, ?string $vendor = null, ?string $trackingNumber = null): OrderShipment
    {
        $data = ['status' => OrderShipmentStatus::Shipped];

        if ($vendor !== null) {
            $data['delivery_vendor'] = $vendor;
        }

        if ($trackingNumber !== null) {
            $data['delivery_reference'] = $trackingNumber;
        }

        $shipment->update($data);

        return $shipment->fresh();
    }

    public function markAsDelivered(OrderShipment $shipment): OrderShipment
    {
        $shipment->update(['status' => OrderShipmentStatus::Delivered]);

        return $shipment->fresh();
    }

    public function getShipmentsByOrder(Order $order): Collection
    {
        return $order->shipments()->orderByDesc('created_at')->get();
    }

    public function isOrderFullyShipped(Order $order): bool
    {
        $shipments = $order->shipments;

        if ($shipments->isEmpty()) {
            return false;
        }

        return $shipments->every(
            fn (OrderShipment $shipment) => $shipment->status === OrderShipmentStatus::Delivered
        );
    }
}
