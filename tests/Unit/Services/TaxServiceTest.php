<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\TaxRateType;
use Appsolutely\AIO\Models\TaxRate;
use Appsolutely\AIO\Services\TaxService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaxService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TaxService::class);
    }

    public function test_calculate_percentage_tax(): void
    {
        TaxRate::factory()->create([
            'country' => 'US',
            'region'  => null,
            'type'    => TaxRateType::Percentage,
            'rate'    => 1000, // 10%
        ]);

        $tax = $this->service->calculateTax(10000, 'US');

        $this->assertEquals(1000, $tax);
    }

    public function test_calculate_fixed_tax(): void
    {
        TaxRate::factory()->create([
            'country' => 'US',
            'region'  => null,
            'type'    => TaxRateType::Fixed,
            'rate'    => 500,
        ]);

        $tax = $this->service->calculateTax(10000, 'US');

        $this->assertEquals(500, $tax);
    }

    public function test_calculate_compound_tax(): void
    {
        TaxRate::factory()->create([
            'country'     => 'CA',
            'region'      => null,
            'type'        => TaxRateType::Percentage,
            'rate'        => 500, // 5%
            'priority'    => 0,
            'is_compound' => false,
        ]);
        TaxRate::factory()->create([
            'country'     => 'CA',
            'region'      => null,
            'type'        => TaxRateType::Percentage,
            'rate'        => 1000, // 10% compound
            'priority'    => 1,
            'is_compound' => true,
        ]);

        // First tax: 10000 * 5% = 500 (not compound, so taxable stays 10000)
        // Second tax: 10000 * 10% = 1000 (compound, but applied on original)
        $tax = $this->service->calculateTax(10000, 'CA');

        $this->assertEquals(1500, $tax);
    }

    public function test_calculate_tax_with_no_matching_rates(): void
    {
        $tax = $this->service->calculateTax(10000, 'ZZ');

        $this->assertEquals(0, $tax);
    }

    public function test_calculate_tax_with_region(): void
    {
        TaxRate::factory()->create([
            'country' => 'US',
            'region'  => 'CA',
            'type'    => TaxRateType::Percentage,
            'rate'    => 800, // 8%
        ]);
        TaxRate::factory()->create([
            'country' => 'US',
            'region'  => null,
            'type'    => TaxRateType::Percentage,
            'rate'    => 500, // 5% federal
        ]);

        // Both should apply (null region matches all, CA region matches specific)
        $tax = $this->service->calculateTax(10000, 'US', 'CA');

        $this->assertEquals(1300, $tax);
    }

    public function test_inactive_rates_are_excluded(): void
    {
        TaxRate::factory()->create([
            'country'   => 'US',
            'type'      => TaxRateType::Percentage,
            'rate'      => 1000,
            'is_active' => false,
        ]);

        $tax = $this->service->calculateTax(10000, 'US');

        $this->assertEquals(0, $tax);
    }
}
