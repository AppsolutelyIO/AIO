<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Enums\ProductType;
use Illuminate\Database\Eloquent\Collection;

interface ShippingServiceInterface
{
    /**
     * Check if a product type requires physical shipping.
     */
    public function requiresShipping(ProductType $productType): bool;

    /**
     * Get available shipping rates for a given country and order amount.
     */
    public function getAvailableRates(string $country, int $orderAmount, ?string $region = null): Collection;

    /**
     * Calculate shipping cost for a specific rate.
     */
    public function calculateShippingCost(int $shippingRateId, int $orderAmount): int;
}
