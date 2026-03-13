<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\OrderPayment;

final class OrderPaymentRepository extends BaseRepository
{
    public function model(): string
    {
        return OrderPayment::class;
    }
}
