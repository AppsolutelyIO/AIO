<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Tests\TestCase;

class CurrencyHelperTest extends TestCase
{
    public function test_currency_subunit_factor_defaults_to_100(): void
    {
        $this->assertEquals(100, currency_subunit_factor('USD'));
        $this->assertEquals(100, currency_subunit_factor('EUR'));
        $this->assertEquals(100, currency_subunit_factor('GBP'));
        $this->assertEquals(100, currency_subunit_factor('CNY'));
        $this->assertEquals(100, currency_subunit_factor('AUD'));
    }

    public function test_currency_subunit_factor_zero_decimal(): void
    {
        $this->assertEquals(1, currency_subunit_factor('JPY'));
        $this->assertEquals(1, currency_subunit_factor('KRW'));
        $this->assertEquals(1, currency_subunit_factor('VND'));
        $this->assertEquals(1, currency_subunit_factor('CLP'));
        $this->assertEquals(1, currency_subunit_factor('XAF'));
    }

    public function test_currency_subunit_factor_three_decimal(): void
    {
        $this->assertEquals(1000, currency_subunit_factor('KWD'));
        $this->assertEquals(1000, currency_subunit_factor('BHD'));
        $this->assertEquals(1000, currency_subunit_factor('OMR'));
        $this->assertEquals(1000, currency_subunit_factor('JOD'));
        $this->assertEquals(1000, currency_subunit_factor('TND'));
    }

    public function test_currency_subunit_factor_is_case_insensitive(): void
    {
        $this->assertEquals(1, currency_subunit_factor('jpy'));
        $this->assertEquals(1000, currency_subunit_factor('kwd'));
        $this->assertEquals(100, currency_subunit_factor('usd'));
    }

    public function test_currency_subunit_factor_defaults_to_app_currency(): void
    {
        config(['appsolutely.currency.code' => 'JPY']);
        $this->assertEquals(1, currency_subunit_factor());

        config(['appsolutely.currency.code' => 'KWD']);
        $this->assertEquals(1000, currency_subunit_factor());

        config(['appsolutely.currency.code' => 'USD']);
        $this->assertEquals(100, currency_subunit_factor());
    }

    public function test_format_cents_two_decimal_currency(): void
    {
        $this->assertEquals('99.90', format_cents(9990, null, 'USD'));
        $this->assertEquals('0.01', format_cents(1, null, 'USD'));
        $this->assertEquals('1,234.56', format_cents(123456, null, 'USD'));
    }

    public function test_format_cents_zero_decimal_currency(): void
    {
        $this->assertEquals('500', format_cents(500, null, 'JPY'));
        $this->assertEquals('1,000', format_cents(1000, null, 'JPY'));
    }

    public function test_format_cents_three_decimal_currency(): void
    {
        $this->assertEquals('1.500', format_cents(1500, null, 'KWD'));
        $this->assertEquals('0.001', format_cents(1, null, 'KWD'));
    }

    public function test_format_cents_null_value(): void
    {
        $this->assertEquals('0.00', format_cents(null, null, 'USD'));
        $this->assertEquals('0', format_cents(null, null, 'JPY'));
        $this->assertEquals('0.000', format_cents(null, null, 'KWD'));
    }

    public function test_format_cents_custom_decimals_override(): void
    {
        $this->assertEquals('99.9', format_cents(9990, 1, 'USD'));
        $this->assertEquals('500.00', format_cents(500, 2, 'JPY'));
    }

    public function test_format_cents_backward_compatible_without_currency(): void
    {
        $this->assertEquals('99.90', format_cents(9990, 2));
        $this->assertEquals('0.00', format_cents(null, 2));
    }

    public function test_subunit_to_display_two_decimal(): void
    {
        $closure = subunit_to_display('USD');
        $this->assertEquals(99.90, $closure(9990));
        $this->assertNull($closure(null));
    }

    public function test_subunit_to_display_zero_decimal(): void
    {
        $closure = subunit_to_display('JPY');
        $this->assertEquals(500, $closure(500));
    }

    public function test_subunit_to_display_three_decimal(): void
    {
        $closure = subunit_to_display('KWD');
        $this->assertEquals(1.5, $closure(1500));
    }

    public function test_display_to_subunit_two_decimal(): void
    {
        $closure = display_to_subunit('USD');
        $this->assertEquals(9990, $closure(99.90));
    }

    public function test_display_to_subunit_zero_decimal(): void
    {
        $closure = display_to_subunit('JPY');
        $this->assertEquals(500, $closure(500));
    }

    public function test_display_to_subunit_three_decimal(): void
    {
        $closure = display_to_subunit('KWD');
        $this->assertEquals(1500, $closure(1.5));
    }

    public function test_subunit_to_display_backward_compatible(): void
    {
        $closure = subunit_to_display();
        $this->assertEquals(99.90, $closure(9990));
    }

    public function test_display_to_subunit_backward_compatible(): void
    {
        $closure = display_to_subunit();
        $this->assertEquals(9990, $closure(99.90));
    }
}
