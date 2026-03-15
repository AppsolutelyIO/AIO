<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\ShippingRateType;
use Appsolutely\AIO\Models\ShippingRate;
use Appsolutely\AIO\Models\ShippingZone;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_zone_has_many_rates(): void
    {
        $zone = ShippingZone::factory()->create();
        ShippingRate::query()->create([
            'shipping_zone_id' => $zone->id,
            'name'             => 'Standard',
            'type'             => ShippingRateType::FlatRate,
            'price'            => 1000,
            'is_active'        => true,
        ]);

        $this->assertCount(1, $zone->rates);
    }

    public function test_shipping_zone_active_rates_filters_inactive(): void
    {
        $zone = ShippingZone::factory()->create();
        ShippingRate::query()->create([
            'shipping_zone_id' => $zone->id,
            'name'             => 'Standard',
            'type'             => ShippingRateType::FlatRate,
            'price'            => 1000,
            'is_active'        => true,
        ]);
        ShippingRate::query()->create([
            'shipping_zone_id' => $zone->id,
            'name'             => 'Express (Disabled)',
            'type'             => ShippingRateType::FlatRate,
            'price'            => 2000,
            'is_active'        => false,
        ]);

        $this->assertCount(2, $zone->rates);
        $this->assertCount(1, $zone->activeRates);
    }

    public function test_shipping_zone_casts_countries_as_array(): void
    {
        $zone = ShippingZone::factory()->create(['countries' => ['US', 'CA']]);

        $this->assertIsArray($zone->countries);
        $this->assertContains('US', $zone->countries);
    }

    public function test_shipping_zone_soft_deletes(): void
    {
        $zone = ShippingZone::factory()->create();
        $zone->delete();

        $this->assertSoftDeleted($zone);
    }

    public function test_shipping_rate_belongs_to_zone(): void
    {
        $zone = ShippingZone::factory()->create();
        $rate = ShippingRate::factory()->create(['shipping_zone_id' => $zone->id]);

        $this->assertInstanceOf(ShippingZone::class, $rate->zone);
        $this->assertEquals($zone->id, $rate->zone->id);
    }

    public function test_shipping_rate_type_cast(): void
    {
        $rate = ShippingRate::factory()->create(['type' => ShippingRateType::FlatRate]);

        $this->assertInstanceOf(ShippingRateType::class, $rate->type);
    }

    public function test_shipping_rate_free_shipping_factory(): void
    {
        $rate = ShippingRate::factory()->freeShipping()->create();

        $this->assertEquals(ShippingRateType::FreeShipping, $rate->type);
        $this->assertEquals(0, $rate->price);
    }
}
