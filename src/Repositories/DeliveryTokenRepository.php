<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\DeliveryToken;

final class DeliveryTokenRepository extends BaseRepository
{
    public function model(): string
    {
        return DeliveryToken::class;
    }
}
