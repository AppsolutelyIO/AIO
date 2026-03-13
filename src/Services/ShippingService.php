<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Enums\ShippingRateType;
use Appsolutely\AIO\Models\ShippingRate;
use Appsolutely\AIO\Models\ShippingZone;
use Appsolutely\AIO\Repositories\ShippingRateRepository;
use Appsolutely\AIO\Repositories\ShippingZoneRepository;
use Appsolutely\AIO\Services\Contracts\ShippingServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class ShippingService implements ShippingServiceInterface
{
    public function __construct(
        protected ShippingZoneRepository $shippingZoneRepository,
        protected ShippingRateRepository $shippingRateRepository,
    ) {}

    public function requiresShipping(ProductType $productType): bool
    {
        return $productType === ProductType::Physical;
    }

    public function getAvailableRates(string $country, int $orderAmount, ?string $region = null): Collection
    {
        $zones = ShippingZone::query()
            ->where('is_active', true)
            ->whereJsonContains('countries', $country)
            ->orderBy('sort')
            ->get();

        if ($zones->isEmpty()) {
            return new Collection();
        }

        return ShippingRate::query()
            ->whereIn('shipping_zone_id', $zones->pluck('id'))
            ->where('is_active', true)
            ->where('min_order_amount', '<=', $orderAmount)
            ->where(function ($query) use ($orderAmount) {
                $query->whereNull('max_order_amount')
                    ->orWhere('max_order_amount', '>=', $orderAmount);
            })
            ->get();
    }

    public function calculateShippingCost(int $shippingRateId, int $orderAmount): int
    {
        $rate = ShippingRate::query()->findOrFail($shippingRateId);

        return match ($rate->type) {
            ShippingRateType::FreeShipping => 0,
            ShippingRateType::FlatRate     => $rate->price,
            ShippingRateType::WeightBased  => $rate->price,
        };
    }
}
