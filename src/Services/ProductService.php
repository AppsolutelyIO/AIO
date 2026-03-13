<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Repositories\ProductRepository;
use Appsolutely\AIO\Repositories\ProductSkuRepository;
use Appsolutely\AIO\Services\Contracts\ProductServiceInterface;
use Illuminate\Support\Collection;

final readonly class ProductService implements ProductServiceInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductSkuRepository $productSkuRepository
    ) {}

    public function getActiveProducts(): Collection
    {
        return $this->productRepository->getActiveProducts();
    }

    public function getActiveSkus(Product $product): Collection
    {
        return $this->productSkuRepository->getActiveSkusByProduct($product->id);
    }

    /**
     * Get product types with translations
     */
    public function getProductTypes(): array
    {
        $types = [];
        foreach (ProductType::cases() as $case) {
            $types[$case->value] = __t($case->label());
        }

        return $types;
    }

    /**
     * Get shipment methods for manual virtual products
     * Moved from Product model to service
     */
    public function getShipmentMethodForManualVirtualProduct(): array
    {
        return Product::SHIPMENT_METHOD_MANUAL_DELIVERABLE_VIRTUAL_PRODUCT;
    }

    /**
     * Get shipment methods for auto virtual products
     * Moved from Product model to service
     */
    public function getShipmentMethodForAutoVirtualProduct(): array
    {
        return Product::SHIPMENT_METHOD_AUTO_DELIVERABLE_VIRTUAL_PRODUCT;
    }

    /**
     * Get shipment methods for physical products
     * Moved from Product model to service
     */
    public function getShipmentMethodForPhysicalProduct(): array
    {
        return Product::SHIPMENT_METHOD_PHYSICAL_PRODUCT;
    }
}
