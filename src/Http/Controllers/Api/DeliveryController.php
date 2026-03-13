<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Api;

use Appsolutely\AIO\Http\Requests\Api\FulfillDeliveryRequest;
use Appsolutely\AIO\Services\Contracts\DeliveryServiceInterface;
use Illuminate\Http\JsonResponse;

final class DeliveryController extends BaseApiController
{
    public function __construct(
        protected DeliveryServiceInterface $deliveryService,
    ) {}

    /**
     * Check delivery token status.
     */
    public function show(string $token): JsonResponse
    {
        $deliveryToken = $this->deliveryService->findByToken($token);

        if (! $deliveryToken) {
            return $this->failNotFound('Delivery token not found.');
        }

        return $this->success([
            'token'         => $deliveryToken->token,
            'order_id'      => $deliveryToken->order_id,
            'order_item_id' => $deliveryToken->order_item_id,
            'product_type'  => $deliveryToken->product_type->value,
            'status'        => $deliveryToken->status->value,
            'expires_at'    => $deliveryToken->expires_at?->toIso8601String(),
            'delivered_at'  => $deliveryToken->delivered_at?->toIso8601String(),
        ]);
    }

    /**
     * Fulfill a virtual product delivery via external API callback.
     */
    public function fulfill(FulfillDeliveryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $deliveryToken = $this->deliveryService->fulfillByToken(
                $validated['token'],
                $validated['delivery_payload'],
                $validated['channel'] ?? null,
                $request->user()?->name ?? $request->ip(),
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->failNotFound('Delivery token not found.');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }

        return $this->success([
            'token'        => $deliveryToken->token,
            'status'       => $deliveryToken->status->value,
            'delivered_at' => $deliveryToken->delivered_at?->toIso8601String(),
        ], 'Delivery fulfilled successfully.');
    }
}
