<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductReview;

final class ProductReviewRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductReview::class;
    }
}
