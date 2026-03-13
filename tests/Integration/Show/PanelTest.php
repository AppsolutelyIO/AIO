<?php

namespace Appsolutely\AIO\Tests\Integration\Show;

use Appsolutely\AIO\Show;
use Appsolutely\AIO\Show\Panel;
use Appsolutely\AIO\Tests\Integration\TestCase;
use Illuminate\Support\Fluent;

class PanelTest extends TestCase
{
    private function createPanel(): Panel
    {
        $show = new Show(new Fluent(['id' => 1]));

        return new Panel($show);
    }

    // --- Panel::hasWrapper() ---

    public function test_has_wrapper_returns_false_by_default()
    {
        $panel = $this->createPanel();
        $this->assertFalse($panel->hasWrapper());
    }

    public function test_has_wrapper_returns_bool_type()
    {
        $panel = $this->createPanel();
        $this->assertIsBool($panel->hasWrapper());
    }

    public function test_has_wrapper_returns_true_after_wrap()
    {
        $panel = $this->createPanel();
        $panel->wrap(function () {
            return 'wrapped';
        });
        $this->assertTrue($panel->hasWrapper());
    }

    public function test_has_wrapper_returns_true_after_wrap_returns_bool_type()
    {
        $panel = $this->createPanel();
        $panel->wrap(function () {
            return 'wrapped';
        });
        $this->assertIsBool($panel->hasWrapper());
    }
}
