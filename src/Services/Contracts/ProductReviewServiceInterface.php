<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\ProductReview;

interface ProductReviewServiceInterface
{
    /**
     * Create a product review.
     *
     * @param  array<string, mixed>  $data
     */
    public function createReview(array $data): ProductReview;

    /**
     * Get the average rating for a product.
     */
    public function getAverageRating(int $productId): float;
}
