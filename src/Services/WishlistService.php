<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Wishlist;
use Appsolutely\AIO\Models\WishlistItem;
use Appsolutely\AIO\Repositories\WishlistItemRepository;
use Appsolutely\AIO\Repositories\WishlistRepository;
use Appsolutely\AIO\Services\Contracts\WishlistServiceInterface;

final readonly class WishlistService implements WishlistServiceInterface
{
    public function __construct(
        protected WishlistRepository $wishlistRepository,
        protected WishlistItemRepository $wishlistItemRepository,
    ) {}

    public function getOrCreateDefault(int $userId): Wishlist
    {
        return Wishlist::query()->firstOrCreate(
            ['user_id' => $userId],
            ['name' => 'Default'],
        );
    }

    public function addItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): WishlistItem
    {
        return WishlistItem::query()->firstOrCreate([
            'wishlist_id'    => $wishlist->id,
            'product_id'     => $productId,
            'product_sku_id' => $productSkuId,
        ]);
    }

    public function removeItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): bool
    {
        return (bool) $wishlist->items()
            ->where('product_id', $productId)
            ->where('product_sku_id', $productSkuId)
            ->delete();
    }

    public function hasItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): bool
    {
        return $wishlist->items()
            ->where('product_id', $productId)
            ->where('product_sku_id', $productSkuId)
            ->exists();
    }
}
