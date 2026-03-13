<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\DeliveryTokenStatus;
use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Repositories\DeliveryTokenRepository;
use Appsolutely\AIO\Services\Contracts\DeliveryServiceInterface;
use Illuminate\Support\Str;

final readonly class DeliveryService implements DeliveryServiceInterface
{
    public function __construct(
        protected DeliveryTokenRepository $deliveryTokenRepository,
    ) {}

    public function createDeliveryToken(Order $order, OrderItem $orderItem): DeliveryToken
    {
        $product     = $orderItem->product;
        $productType = $product->type;

        if ($productType === ProductType::Physical) {
            throw new \InvalidArgumentException('Physical products do not use delivery tokens. Use shipping instead.');
        }

        return DeliveryToken::query()->create([
            'order_id'      => $order->id,
            'order_item_id' => $orderItem->id,
            'token'         => Str::random(64),
            'product_type'  => $productType->value,
            'status'        => DeliveryTokenStatus::Pending,
            'expires_at'    => now()->addDays(7),
        ]);
    }

    public function fulfillByToken(
        string $token,
        string $deliveryPayload,
        ?string $channel = null,
        ?string $deliveredBy = null,
    ): DeliveryToken {
        $deliveryToken = DeliveryToken::query()
            ->where('token', $token)
            ->firstOrFail();

        if (! $deliveryToken->isDeliverable()) {
            throw new \InvalidArgumentException('Token is not deliverable. Status: ' . $deliveryToken->status->value);
        }

        $deliveryToken->update([
            'status'           => DeliveryTokenStatus::Delivered,
            'delivery_payload' => $deliveryPayload,
            'delivery_channel' => $channel,
            'delivered_by'     => $deliveredBy,
            'delivered_at'     => now(),
        ]);

        return $deliveryToken->fresh();
    }

    public function findByToken(string $token): ?DeliveryToken
    {
        return DeliveryToken::query()->where('token', $token)->first();
    }

    public function isOrderFullyDelivered(Order $order): bool
    {
        $tokens = $order->deliveryTokens;

        if ($tokens->isEmpty()) {
            return false;
        }

        return $tokens->every(fn (DeliveryToken $token) => $token->status === DeliveryTokenStatus::Delivered);
    }
}
