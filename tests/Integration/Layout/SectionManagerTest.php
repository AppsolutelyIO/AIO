<?php

namespace Appsolutely\AIO\Tests\Integration\Layout;

use Appsolutely\AIO\Layout\SectionManager;
use Appsolutely\AIO\Tests\Unit\TestCase;

class SectionManagerTest extends TestCase
{
    protected function createManager(): SectionManager
    {
        return new SectionManager();
    }

    // --- Basic inject/yield ---

    public function test_inject_and_yield()
    {
        $mgr = $this->createManager();
        $mgr->inject('header', 'Hello');
        $this->assertSame('Hello', $mgr->yieldContent('header'));
    }

    public function test_yield_missing_section_returns_default()
    {
        $mgr = $this->createManager();
        $this->assertSame('fallback', $mgr->yieldContent('missing', 'fallback'));
    }

    public function test_yield_missing_section_returns_empty()
    {
        $mgr = $this->createManager();
        $this->assertSame('', $mgr->yieldContent('missing'));
    }

    // --- Append ---

    public function test_inject_appends_by_default()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', 'A');
        $mgr->inject('section', 'B');
        $mgr->inject('section', 'C');
        $this->assertSame('ABC', $mgr->yieldContent('section'));
    }

    // --- Overwrite ---

    public function test_inject_overwrite()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', 'first');
        $mgr->inject('section', 'second', false);
        $this->assertSame('second', $mgr->yieldContent('section'));
    }

    // --- Callable ---

    public function test_inject_callable()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', function () {
            return 'from callable';
        });
        $this->assertSame('from callable', $mgr->yieldContent('section'));
    }

    public function test_inject_callable_with_options()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', function ($opts) {
            return "name:{$opts->name}";
        });
        $this->assertSame('name:John', $mgr->yieldContent('section', '', ['name' => 'John']));
    }

    public function test_inject_callable_overwrite_with_previous()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', 'original');
        $mgr->inject('section', function ($opts) {
            return $opts->previous . '_modified';
        }, false);

        $this->assertSame('original_modified', $mgr->yieldContent('section'));
    }

    // --- Priority ---

    public function test_inject_with_priority_sorting()
    {
        $mgr = $this->createManager();
        // Higher priority comes first
        $mgr->inject('section', 'C', true, 1);
        $mgr->inject('section', 'A', true, 3);
        $mgr->inject('section', 'B', true, 2);

        $this->assertSame('ABC', $mgr->yieldContent('section'));
    }

    // --- hasSection ---

    public function test_has_section()
    {
        $mgr = $this->createManager();
        $this->assertFalse($mgr->hasSection('foo'));

        $mgr->inject('foo', 'bar');
        $this->assertTrue($mgr->hasSection('foo'));
    }

    // --- Default sections ---

    public function test_inject_default()
    {
        $mgr = $this->createManager();
        $mgr->injectDefault('section', 'default value');
        $this->assertSame('default value', $mgr->yieldContent('section'));
    }

    public function test_inject_default_overridden_by_regular_inject()
    {
        $mgr = $this->createManager();
        $mgr->injectDefault('section', 'default');
        $mgr->inject('section', 'override');

        $this->assertSame('override', $mgr->yieldContent('section'));
    }

    public function test_inject_default_does_not_override_existing()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', 'existing');
        $mgr->injectDefault('section', 'default');

        $this->assertSame('existing', $mgr->yieldContent('section'));
    }

    public function test_inject_default_callable()
    {
        $mgr = $this->createManager();
        $mgr->injectDefault('section', function ($opts) {
            return 'Hello ' . $opts->name;
        });

        $this->assertSame('Hello World', $mgr->yieldContent('section', '', ['name' => 'World']));
    }

    public function test_has_default_section()
    {
        $mgr = $this->createManager();
        $this->assertFalse($mgr->hasDefaultSection('test'));

        $mgr->injectDefault('test', 'val');
        $this->assertTrue($mgr->hasDefaultSection('test'));
    }

    // --- Flush ---

    public function test_flush_sections()
    {
        $mgr = $this->createManager();
        $mgr->inject('a', '1');
        $mgr->injectDefault('b', '2');

        $mgr->flushSections();

        $this->assertFalse($mgr->hasSection('a'));
        $this->assertFalse($mgr->hasDefaultSection('b'));
        $this->assertSame('', $mgr->yieldContent('a'));
    }

    // --- Empty string injection ---

    public function test_inject_empty_string_creates_section()
    {
        $mgr = $this->createManager();
        $mgr->inject('section', '');
        $this->assertTrue($mgr->hasSection('section'));
        $this->assertSame('', $mgr->yieldContent('section'));
    }

    public function test_inject_empty_overrides_default()
    {
        $mgr = $this->createManager();
        $mgr->injectDefault('section', 'default');
        $mgr->inject('section', '');

        // Empty inject should still take precedence over default
        $this->assertSame('', $mgr->yieldContent('section'));
    }
}
