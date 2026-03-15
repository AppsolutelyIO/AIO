<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Enums\ShippingRateType;
use Appsolutely\AIO\Models\ShippingRate;
use Appsolutely\AIO\Models\ShippingZone;
use Appsolutely\AIO\Services\ShippingService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShippingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ShippingService::class);
    }

    public function test_requires_shipping_for_physical(): void
    {
        $this->assertTrue($this->service->requiresShipping(ProductType::Physical));
    }

    public function test_does_not_require_shipping_for_auto_virtual(): void
    {
        $this->assertFalse($this->service->requiresShipping(ProductType::AutoVirtual));
    }

    public function test_does_not_require_shipping_for_manual_virtual(): void
    {
        $this->assertFalse($this->service->requiresShipping(ProductType::ManualVirtual));
    }

    public function test_get_available_rates_for_country(): void
    {
        $zone = ShippingZone::factory()->create(['countries' => ['US', 'CA']]);
        ShippingRate::factory()->create([
            'shipping_zone_id' => $zone->id,
            'min_order_amount' => 0,
        ]);

        $rates = $this->service->getAvailableRates('US', 5000);

        $this->assertCount(1, $rates);
    }

    public function test_get_available_rates_returns_empty_for_unknown_country(): void
    {
        ShippingZone::factory()->create(['countries' => ['US']]);

        $rates = $this->service->getAvailableRates('JP', 5000);

        $this->assertCount(0, $rates);
    }

    public function test_get_available_rates_filters_inactive_zones(): void
    {
        $zone = ShippingZone::factory()->inactive()->create(['countries' => ['US']]);
        ShippingRate::factory()->create(['shipping_zone_id' => $zone->id]);

        $rates = $this->service->getAvailableRates('US', 5000);

        $this->assertCount(0, $rates);
    }

    public function test_get_available_rates_filters_min_order_amount(): void
    {
        $zone = ShippingZone::factory()->create(['countries' => ['US']]);
        ShippingRate::factory()->create([
            'shipping_zone_id' => $zone->id,
            'min_order_amount' => 10000,
        ]);

        $rates = $this->service->getAvailableRates('US', 5000);
        $this->assertCount(0, $rates);

        $rates = $this->service->getAvailableRates('US', 15000);
        $this->assertCount(1, $rates);
    }

    public function test_calculate_shipping_cost_flat_rate(): void
    {
        $rate = ShippingRate::factory()->create([
            'type'  => ShippingRateType::FlatRate,
            'price' => 1500,
        ]);

        $cost = $this->service->calculateShippingCost($rate->id, 10000);

        $this->assertEquals(1500, $cost);
    }

    public function test_calculate_shipping_cost_free_shipping(): void
    {
        $rate = ShippingRate::factory()->freeShipping()->create();

        $cost = $this->service->calculateShippingCost($rate->id, 10000);

        $this->assertEquals(0, $cost);
    }
}
