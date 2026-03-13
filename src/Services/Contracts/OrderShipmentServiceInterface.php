<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderShipment;
use Illuminate\Database\Eloquent\Collection;

interface OrderShipmentServiceInterface
{
    /**
     * Create a shipment for an order.
     *
     * @param  array<string, mixed>  $shippingData
     */
    public function createShipment(Order $order, array $shippingData): OrderShipment;

    /**
     * Update a shipment's status.
     */
    public function updateStatus(OrderShipment $shipment, OrderShipmentStatus $status): OrderShipment;

    /**
     * Mark a shipment as shipped with tracking info.
     */
    public function markAsShipped(OrderShipment $shipment, ?string $vendor = null, ?string $trackingNumber = null): OrderShipment;

    /**
     * Mark a shipment as delivered.
     */
    public function markAsDelivered(OrderShipment $shipment): OrderShipment;

    /**
     * Get all shipments for an order.
     *
     * @return Collection<int, OrderShipment>
     */
    public function getShipmentsByOrder(Order $order): Collection;

    /**
     * Check if all shipments for an order have been delivered.
     */
    public function isOrderFullyShipped(Order $order): bool;
}
