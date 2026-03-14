<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;

interface CouponServiceInterface
{
    /**
     * Validate and apply a coupon to calculate discount amount.
     */
    public function calculateDiscount(Coupon $coupon, int $orderAmount): int;

    /**
     * Check if a coupon is valid for the given user.
     */
    public function isValidForUser(Coupon $coupon, Authenticatable $user): bool;

    /**
     * Record coupon usage after order is placed.
     */
    public function recordUsage(Coupon $coupon, Authenticatable $user, Order $order, int $discountAmount): void;
}
