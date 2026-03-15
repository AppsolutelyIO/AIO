<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\ColorHelper;
use Appsolutely\AIO\Support\Helper;
use Appsolutely\AIO\Tests\Unit\TestCase;

class ColorHelperTest extends TestCase
{
    public function test_lighten()
    {
        $result = ColorHelper::lighten('#333333', 10);
        $this->assertStringStartsWith('#', $result);
        $this->assertNotSame('#333333', $result);
    }

    public function test_lighten_zero_returns_same()
    {
        $this->assertSame('#333333', ColorHelper::lighten('#333333', 0));
    }

    public function test_darken()
    {
        $result = ColorHelper::darken('#cccccc', 10);
        $this->assertStringStartsWith('#', $result);
    }

    public function test_alpha()
    {
        $result = ColorHelper::alpha('#3085d6', 0.5);
        $this->assertStringContainsString('rgba(', $result);
        $this->assertStringContainsString('0.5', $result);
    }

    public function test_alpha_full_opacity_returns_original()
    {
        $this->assertSame('#3085d6', ColorHelper::alpha('#3085d6', 1));
    }

    public function test_to_rgb()
    {
        [$r, $b, $g] = ColorHelper::toRGB('ff0000');
        $this->assertSame(255, $r);
        $this->assertSame(0, $b);
        $this->assertSame(0, $g);
    }

    public function test_to_rgb_with_amount()
    {
        [$r, $b, $g] = ColorHelper::toRGB('000000', 10);
        $this->assertSame(10, $r);
        $this->assertSame(10, $b);
        $this->assertSame(10, $g);
    }

    public function test_to_rgb_clamps_values()
    {
        [$r, $b, $g] = ColorHelper::toRGB('ffffff', 10);
        $this->assertSame(255, $r);
        $this->assertSame(255, $b);
        $this->assertSame(255, $g);

        [$r, $b, $g] = ColorHelper::toRGB('000000', -10);
        $this->assertSame(0, $r);
        $this->assertSame(0, $b);
        $this->assertSame(0, $g);
    }

    // Verify Helper still delegates correctly
    public function test_helper_delegates_color_lighten()
    {
        $helper = Helper::colorLighten('#333333', 10);
        $direct = ColorHelper::lighten('#333333', 10);
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_color_darken()
    {
        $helper = Helper::colorDarken('#cccccc', 10);
        $direct = ColorHelper::darken('#cccccc', 10);
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_color_alpha()
    {
        $helper = Helper::colorAlpha('#3085d6', 0.5);
        $direct = ColorHelper::alpha('#3085d6', 0.5);
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_color_to_rgb()
    {
        $helper = Helper::colorToRBG('ff0000');
        $direct = ColorHelper::toRGB('ff0000');
        $this->assertSame($direct, $helper);
    }
}
