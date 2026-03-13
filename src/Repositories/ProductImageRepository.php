<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductImage;

final class ProductImageRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductImage::class;
    }
}
