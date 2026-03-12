<?php

namespace Appsolutely\AIO\Tests\Unit;

use Appsolutely\AIO\Color;

class ColorTest extends TestCase
{
    protected function createColor(string $name = 'default'): Color
    {
        $color = new Color();
        $color->setName($name);

        return $color;
    }

    public function test_get_name_default()
    {
        $color = new Color();
        $color->setName('default');
        $this->assertSame('default', $color->getName());
    }

    public function test_set_name()
    {
        $color = $this->createColor('blue');
        $this->assertSame('blue', $color->getName());
    }

    public function test_get_returns_color_value()
    {
        $color = $this->createColor();
        // 'red' is defined in $allColors as '#ea5455'
        $this->assertSame('#ea5455', $color->get('red'));
    }

    public function test_get_returns_default_for_missing()
    {
        $color = $this->createColor();
        $this->assertSame('#000', $color->get('nonexistent', '#000'));
    }

    public function test_get_returns_null_for_missing_without_default()
    {
        $color = $this->createColor();
        $this->assertNull($color->get('nonexistent'));
    }

    public function test_get_resolves_alias()
    {
        $color = $this->createColor();
        // 'info' maps to 'blue' which maps to '#3085d6'
        $result = $color->get('info');
        $this->assertSame('#3085d6', $result);
    }

    public function test_all_returns_resolved_colors()
    {
        $color = $this->createColor();
        $all = $color->all();

        $this->assertIsArray($all);
        // 'info' should be resolved to blue's actual hex value
        $this->assertSame('#3085d6', $all['info']);
        // Direct color should be present
        $this->assertSame('#ea5455', $all['red']);
    }

    public function test_default_theme_has_primary_color()
    {
        $color = $this->createColor();
        $this->assertSame('#586cb1', $color->get('primary'));
    }

    public function test_green_theme_has_different_primary()
    {
        $color = $this->createColor('green');
        $this->assertSame('#4e9876', $color->get('primary'));
    }

    public function test_lighten()
    {
        $color = $this->createColor();
        $result = $color->lighten('red', 20);
        $this->assertIsString($result);
        $this->assertNotSame('#ea5455', $result);
    }

    public function test_darken()
    {
        $color = $this->createColor();
        $result = $color->darken('red', 20);
        $this->assertIsString($result);
        $this->assertNotSame('#ea5455', $result);
    }

    public function test_alpha()
    {
        $color = $this->createColor();
        $result = $color->alpha('red', 0.5);
        $this->assertStringContainsString('rgba(', $result);
        $this->assertStringContainsString('0.5', $result);
    }

    public function test_magic_call_method()
    {
        $color = $this->createColor();
        // __call converts method name to slug, e.g. red() -> darken('red', 0)
        $result = $color->red();
        $this->assertSame('#ea5455', $result);
    }

    public function test_magic_call_with_amount()
    {
        $color = $this->createColor();
        $result = $color->red(10);
        $this->assertIsString($result);
        // Should be darker than original
        $this->assertNotSame('#ea5455', $result);
    }

    public function test_extend_adds_new_theme()
    {
        Color::extend('custom-theme', [
            'primary' => '#ff0000',
            'primary-darker' => '#cc0000',
        ]);

        $color = $this->createColor('custom-theme');
        $this->assertSame('#ff0000', $color->get('primary'));
    }
}
