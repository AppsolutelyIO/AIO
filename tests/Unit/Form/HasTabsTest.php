<?php

namespace Appsolutely\AIO\Tests\Unit\Form;

use Appsolutely\AIO\Tests\Unit\TestCase;

class HasTabsTest extends TestCase
{
    private function createInstance()
    {
        return new class {
            use \Appsolutely\AIO\Form\Concerns\HasTabs;

            // Stub getTab dependency - HasTabs::getTab creates a Tab which needs a form
            // We only test hasTab() which reads $this->tab directly
        };
    }

    // --- HasTabs::hasTab() ---

    public function test_has_tab_returns_false_by_default()
    {
        $instance = $this->createInstance();
        $this->assertFalse($instance->hasTab());
    }

    public function test_has_tab_returns_bool_type()
    {
        $instance = $this->createInstance();
        $this->assertIsBool($instance->hasTab());
    }

    public function test_has_tab_returns_true_when_tab_is_set()
    {
        $instance = $this->createInstance();

        // Use reflection to set the tab property
        $ref = new \ReflectionProperty($instance, 'tab');
        $ref->setAccessible(true);
        $ref->setValue($instance, new \stdClass());

        $this->assertTrue($instance->hasTab());
    }

    public function test_has_tab_returns_bool_type_when_truthy()
    {
        $instance = $this->createInstance();

        $ref = new \ReflectionProperty($instance, 'tab');
        $ref->setAccessible(true);
        $ref->setValue($instance, new \stdClass());

        $this->assertIsBool($instance->hasTab());
    }
}
