<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Models\ProductReview;
use Appsolutely\AIO\Repositories\ProductReviewRepository;
use Appsolutely\AIO\Services\Contracts\ProductReviewServiceInterface;

final readonly class ProductReviewService implements ProductReviewServiceInterface
{
    public function __construct(
        protected ProductReviewRepository $productReviewRepository,
    ) {}

    public function createReview(array $data): ProductReview
    {
        return ProductReview::query()->create($data);
    }

    public function getAverageRating(int $productId): float
    {
        return (float) ProductReview::query()
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::Approved)
            ->avg('rating');
    }
}
