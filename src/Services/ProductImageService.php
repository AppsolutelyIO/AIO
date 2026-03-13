<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\ProductImage;
use Appsolutely\AIO\Repositories\ProductImageRepository;
use Appsolutely\AIO\Services\Contracts\ProductImageServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class ProductImageService implements ProductImageServiceInterface
{
    public function __construct(
        protected ProductImageRepository $productImageRepository,
    ) {}

    public function getImagesByProduct(int $productId): Collection
    {
        return ProductImage::query()
            ->where('product_id', $productId)
            ->orderBy('sort')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPrimaryImage(int $productId): ?ProductImage
    {
        return ProductImage::query()
            ->where('product_id', $productId)
            ->where('is_primary', true)
            ->first();
    }

    public function addImage(int $productId, array $data): ProductImage
    {
        return ProductImage::query()->create(array_merge(
            $data,
            ['product_id' => $productId],
        ));
    }

    public function updateImage(ProductImage $image, array $data): ProductImage
    {
        $image->update($data);

        return $image->fresh();
    }

    public function deleteImage(ProductImage $image): bool
    {
        return (bool) $image->delete();
    }

    public function setPrimaryImage(ProductImage $image): ProductImage
    {
        // Unset current primary images for this product
        ProductImage::query()
            ->where('product_id', $image->product_id)
            ->where('is_primary', true)
            ->where('id', '!=', $image->id)
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);

        return $image->fresh();
    }
}
