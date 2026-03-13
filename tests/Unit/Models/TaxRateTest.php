<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\TaxRateType;
use Appsolutely\AIO\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class TaxRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_tax_rate_calculates_percentage_tax(): void
    {
        $taxRate = TaxRate::factory()->create([
            'type' => TaxRateType::Percentage,
            'rate' => 1000, // 10% (basis points)
        ]);

        $this->assertEquals(1000, $taxRate->calculateTax(10000));
    }

    public function test_tax_rate_calculates_fixed_tax(): void
    {
        $taxRate = TaxRate::factory()->fixed()->create([
            'rate' => 500,
        ]);

        $this->assertEquals(500, $taxRate->calculateTax(10000));
    }

    public function test_tax_rate_type_cast(): void
    {
        $taxRate = TaxRate::factory()->create();

        $this->assertInstanceOf(TaxRateType::class, $taxRate->type);
    }

    public function test_tax_rate_boolean_casts(): void
    {
        $taxRate = TaxRate::factory()->compound()->create();

        $this->assertTrue($taxRate->is_compound);
        $this->assertTrue($taxRate->is_active);
    }

    public function test_tax_rate_soft_deletes(): void
    {
        $taxRate = TaxRate::factory()->create();
        $taxRate->delete();

        $this->assertSoftDeleted($taxRate);
    }
}
