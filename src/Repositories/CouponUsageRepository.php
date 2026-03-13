<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\CouponUsage;

final class CouponUsageRepository extends BaseRepository
{
    public function model(): string
    {
        return CouponUsage::class;
    }
}
