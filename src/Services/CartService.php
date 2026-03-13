<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\CartStatus;
use Appsolutely\AIO\Models\Cart;
use Appsolutely\AIO\Repositories\CartItemRepository;
use Appsolutely\AIO\Repositories\CartRepository;
use Appsolutely\AIO\Services\Contracts\CartServiceInterface;

final readonly class CartService implements CartServiceInterface
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected CartItemRepository $cartItemRepository,
    ) {}

    public function getActiveCart(?int $userId = null, ?string $sessionId = null): Cart
    {
        $query = Cart::query()->where('status', CartStatus::Active);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->firstOrCreate(
            array_filter([
                'user_id'    => $userId,
                'session_id' => $sessionId,
                'status'     => CartStatus::Active,
            ]),
            [
                'total_amount' => 0,
            ]
        );
    }

    public function mergeCarts(Cart $guestCart, Cart $userCart): Cart
    {
        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('product_sku_id', $guestItem->product_sku_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity'    => $existingItem->quantity + $guestItem->quantity,
                    'total_price' => ($existingItem->quantity + $guestItem->quantity) * $existingItem->unit_price,
                ]);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->update(['status' => CartStatus::Abandoned]);

        return $userCart->fresh(['items']);
    }
}
