<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ShippingZone;

final class ShippingZoneRepository extends BaseRepository
{
    public function model(): string
    {
        return ShippingZone::class;
    }
}
