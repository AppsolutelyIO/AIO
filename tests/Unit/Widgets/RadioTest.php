<?php

namespace Appsolutely\AIO\Tests\Unit\Widgets;

use Appsolutely\AIO\Tests\Unit\TestCase;
use Appsolutely\AIO\Widgets\Radio;

class RadioTest extends TestCase
{
    // --- Radio::inline() ---

    public function test_inline_sets_true_by_default()
    {
        $radio = new Radio();
        $radio->inline();

        $vars = $radio->defaultVariables();
        $this->assertTrue($vars['inline']);
    }

    public function test_inline_sets_false_when_passed_false()
    {
        $radio = new Radio();
        $radio->inline(false);

        $vars = $radio->defaultVariables();
        $this->assertFalse($vars['inline']);
    }

    public function test_inline_returns_fluent_self()
    {
        $radio  = new Radio();
        $result = $radio->inline();
        $this->assertSame($radio, $result);
    }

    public function test_inline_default_is_false()
    {
        $radio = new Radio();
        $vars  = $radio->defaultVariables();
        $this->assertFalse($vars['inline']);
    }
}
