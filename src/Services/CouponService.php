<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\CouponType;
use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\CouponUsage;
use Appsolutely\AIO\Models\Order;
use App\Models\User;
use Appsolutely\AIO\Repositories\CouponRepository;
use Appsolutely\AIO\Repositories\CouponUsageRepository;
use Appsolutely\AIO\Services\Contracts\CouponServiceInterface;

final readonly class CouponService implements CouponServiceInterface
{
    public function __construct(
        protected CouponRepository $couponRepository,
        protected CouponUsageRepository $couponUsageRepository,
    ) {}

    public function calculateDiscount(Coupon $coupon, int $orderAmount): int
    {
        if ($orderAmount < $coupon->min_order_amount) {
            return 0;
        }

        $discount = match ($coupon->type) {
            CouponType::FixedAmount => $coupon->value,
            CouponType::Percentage  => (int) floor($orderAmount * $coupon->value / BASIS_POINTS_DIVISOR),
        };

        if ($coupon->max_discount_amount !== null) {
            $discount = min($discount, $coupon->max_discount_amount);
        }

        return min($discount, $orderAmount);
    }

    public function isValidForUser(Coupon $coupon, User $user): bool
    {
        if (! $coupon->isValid()) {
            return false;
        }

        if ($coupon->usage_per_user === null) {
            return true;
        }

        $userUsageCount = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        return $userUsageCount < $coupon->usage_per_user;
    }

    public function recordUsage(Coupon $coupon, User $user, Order $order, int $discountAmount): void
    {
        CouponUsage::query()->create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,
            'order_id'        => $order->id,
            'discount_amount' => $discountAmount,
        ]);

        $coupon->increment('used_count');
    }
}
