<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Cart;

interface CartServiceInterface
{
    /**
     * Get or create an active cart for the given user or session.
     */
    public function getActiveCart(?int $userId = null, ?string $sessionId = null): Cart;

    /**
     * Merge a guest cart into a user's cart upon login.
     */
    public function mergeCarts(Cart $guestCart, Cart $userCart): Cart;
}
