<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;

interface ProductImageServiceInterface
{
    /**
     * Get all images for a product, ordered by sort.
     *
     * @return Collection<int, ProductImage>
     */
    public function getImagesByProduct(int $productId): Collection;

    /**
     * Get the primary image for a product.
     */
    public function getPrimaryImage(int $productId): ?ProductImage;

    /**
     * Add an image to a product.
     *
     * @param  array<string, mixed>  $data
     */
    public function addImage(int $productId, array $data): ProductImage;

    /**
     * Update an existing image.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateImage(ProductImage $image, array $data): ProductImage;

    /**
     * Delete an image.
     */
    public function deleteImage(ProductImage $image): bool;

    /**
     * Set an image as the primary image for its product.
     */
    public function setPrimaryImage(ProductImage $image): ProductImage;
}
