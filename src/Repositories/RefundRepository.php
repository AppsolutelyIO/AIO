<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Refund;

final class RefundRepository extends BaseRepository
{
    public function model(): string
    {
        return Refund::class;
    }
}
