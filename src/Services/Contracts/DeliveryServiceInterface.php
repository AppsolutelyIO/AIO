<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;

interface DeliveryServiceInterface
{
    /**
     * Create a delivery token for a virtual order item.
     */
    public function createDeliveryToken(Order $order, OrderItem $orderItem): DeliveryToken;

    /**
     * Fulfill a delivery via external API callback using a token.
     */
    public function fulfillByToken(string $token, string $deliveryPayload, ?string $channel = null, ?string $deliveredBy = null): DeliveryToken;

    /**
     * Find a delivery token by its token string.
     */
    public function findByToken(string $token): ?DeliveryToken;

    /**
     * Check if all items in an order have been delivered.
     */
    public function isOrderFullyDelivered(Order $order): bool;
}
