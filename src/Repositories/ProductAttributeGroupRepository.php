<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductAttributeGroup;

final class ProductAttributeGroupRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductAttributeGroup::class;
    }
}
