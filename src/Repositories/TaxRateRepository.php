<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\TaxRate;

final class TaxRateRepository extends BaseRepository
{
    public function model(): string
    {
        return TaxRate::class;
    }
}
