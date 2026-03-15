<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use App\Models\User;
use Appsolutely\AIO\Enums\CouponStatus;
use Appsolutely\AIO\Enums\CouponType;
use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Models\CouponUsage;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_can_be_created_with_factory(): void
    {
        $coupon = Coupon::factory()->create();

        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
        $this->assertEquals(CouponStatus::Active, $coupon->status);
    }

    public function test_is_valid_returns_true_for_active_coupon(): void
    {
        $coupon = Coupon::factory()->create([
            'status'     => CouponStatus::Active,
            'starts_at'  => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_is_valid_returns_false_for_inactive_coupon(): void
    {
        $coupon = Coupon::factory()->inactive()->create();

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_for_expired_coupon(): void
    {
        $coupon = Coupon::factory()->expired()->create();

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_when_not_yet_started(): void
    {
        $coupon = Coupon::factory()->create([
            'starts_at' => now()->addDay(),
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_when_usage_limit_reached(): void
    {
        $coupon = Coupon::factory()->create([
            'usage_limit' => 5,
            'used_count'  => 5,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_has_many_usages(): void
    {
        $coupon = Coupon::factory()->create();
        $user   = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $user->id]);

        CouponUsage::create([
            'coupon_id'       => $coupon->id,
            'user_id'         => $user->id,
            'order_id'        => $order->id,
            'discount_amount' => 1000,
        ]);

        $this->assertCount(1, $coupon->usages);
    }

    public function test_percentage_factory_state(): void
    {
        $coupon = Coupon::factory()->percentage(15)->create();
        $coupon->refresh();

        $this->assertEquals(CouponType::Percentage, $coupon->type);
        $this->assertEquals(1500, $coupon->value);
    }
}
