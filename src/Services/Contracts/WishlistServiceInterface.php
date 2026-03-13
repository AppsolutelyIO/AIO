<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Wishlist;
use Appsolutely\AIO\Models\WishlistItem;

interface WishlistServiceInterface
{
    /**
     * Get or create the default wishlist for a user.
     */
    public function getOrCreateDefault(int $userId): Wishlist;

    /**
     * Add a product to the user's wishlist.
     */
    public function addItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): WishlistItem;

    /**
     * Remove a product from the wishlist.
     */
    public function removeItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): bool;

    /**
     * Check if a product is in the wishlist.
     */
    public function hasItem(Wishlist $wishlist, int $productId, ?int $productSkuId = null): bool;
}
