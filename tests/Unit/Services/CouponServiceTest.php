<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use App\Models\User;
use Appsolutely\AIO\Enums\CouponType;
use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\CouponUsage;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Services\CouponService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CouponService::class);
    }

    public function test_calculate_discount_fixed_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'type'  => CouponType::FixedAmount,
            'value' => 2000,
        ]);
        $coupon->refresh();

        $discount = $this->service->calculateDiscount($coupon, 10000);

        $this->assertEquals(2000, $discount);
    }

    public function test_calculate_discount_percentage(): void
    {
        $coupon = Coupon::factory()->create([
            'type'  => CouponType::Percentage,
            'value' => 1000, // 10% (1000 basis points)
        ]);
        $coupon->refresh();

        $discount = $this->service->calculateDiscount($coupon, 50000);

        $this->assertEquals(5000, $discount);
    }

    public function test_calculate_discount_respects_max_discount_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'type'                => CouponType::Percentage,
            'value'               => 5000, // 50%
            'max_discount_amount' => 3000,
        ]);
        $coupon->refresh();

        $discount = $this->service->calculateDiscount($coupon, 20000);

        $this->assertEquals(3000, $discount);
    }

    public function test_calculate_discount_returns_zero_below_min_order_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'type'             => CouponType::FixedAmount,
            'value'            => 2000,
            'min_order_amount' => 10000,
        ]);
        $coupon->refresh();

        $discount = $this->service->calculateDiscount($coupon, 5000);

        $this->assertEquals(0, $discount);
    }

    public function test_calculate_discount_does_not_exceed_order_amount(): void
    {
        $coupon = Coupon::factory()->create([
            'type'  => CouponType::FixedAmount,
            'value' => 10000,
        ]);
        $coupon->refresh();

        $discount = $this->service->calculateDiscount($coupon, 5000);

        $this->assertEquals(5000, $discount);
    }

    public function test_is_valid_for_user_returns_true_when_within_limit(): void
    {
        $coupon = Coupon::factory()->create(['usage_per_user' => 3]);
        $user   = User::factory()->create();

        $this->assertTrue($this->service->isValidForUser($coupon, $user));
    }

    public function test_is_valid_for_user_returns_false_when_usage_exceeded(): void
    {
        $coupon = Coupon::factory()->create(['usage_per_user' => 1]);
        $user   = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $user->id]);

        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,
            'order_id'        => $order->id,
            'discount_amount' => 1000,
        ]);

        $this->assertFalse($this->service->isValidForUser($coupon, $user));
    }

    public function test_is_valid_for_user_allows_high_usage_limit(): void
    {
        $coupon = Coupon::factory()->create(['usage_per_user' => 999]);
        $user   = User::factory()->create();

        $this->assertTrue($this->service->isValidForUser($coupon, $user));
    }

    public function test_is_valid_for_user_returns_false_for_inactive_coupon(): void
    {
        $coupon = Coupon::factory()->inactive()->create();
        $user   = User::factory()->create();

        $this->assertFalse($this->service->isValidForUser($coupon, $user));
    }

    public function test_record_usage_creates_usage_and_increments_count(): void
    {
        $coupon = Coupon::factory()->create(['used_count' => 0]);
        $user   = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $user->id]);

        $this->service->recordUsage($coupon, $user, $order, 2000);

        $this->assertDatabaseHas('coupon_usages', [
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
        ]);

        $coupon->refresh();
        $this->assertEquals(1, $coupon->used_count);
    }
}
