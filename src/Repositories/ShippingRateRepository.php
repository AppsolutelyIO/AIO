<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ShippingRate;

final class ShippingRateRepository extends BaseRepository
{
    public function model(): string
    {
        return ShippingRate::class;
    }
}
