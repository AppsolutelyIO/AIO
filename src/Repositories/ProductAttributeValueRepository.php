<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductAttributeValue;

final class ProductAttributeValueRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductAttributeValue::class;
    }
}
