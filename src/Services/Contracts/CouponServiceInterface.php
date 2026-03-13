<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\Order;
use App\Models\User;

interface CouponServiceInterface
{
    /**
     * Validate and apply a coupon to calculate discount amount.
     */
    public function calculateDiscount(Coupon $coupon, int $orderAmount): int;

    /**
     * Check if a coupon is valid for the given user.
     */
    public function isValidForUser(Coupon $coupon, User $user): bool;

    /**
     * Record coupon usage after order is placed.
     */
    public function recordUsage(Coupon $coupon, User $user, Order $order, int $discountAmount): void;
}
