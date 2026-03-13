<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Coupon;

final class CouponRepository extends BaseRepository
{
    public function model(): string
    {
        return Coupon::class;
    }
}
