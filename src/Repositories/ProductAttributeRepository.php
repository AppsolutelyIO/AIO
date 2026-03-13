<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductAttribute;

final class ProductAttributeRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductAttribute::class;
    }
}
