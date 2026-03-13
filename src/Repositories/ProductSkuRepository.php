<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductSku;
use Illuminate\Database\Eloquent\Collection;

final class ProductSkuRepository extends BaseRepository
{
    public function model(): string
    {
        return ProductSku::class;
    }

    public function getActiveSkusByProduct(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)
            ->status()
            ->orderBy('sort')
            ->get();
    }

    public function getSkusBySkuKey(string $attributeKey, string $productId): ?ProductSku
    {
        return $this->model->where('product_id', $productId)
            ->where(function ($query) use ($attributeKey) {

                $query->whereJsonContains('attributes->key', $attributeKey);

            })->first();
    }
}
