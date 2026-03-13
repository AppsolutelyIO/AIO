<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\Setting;
use Appsolutely\AIO\Tests\Unit\TestCase;

class SettingTest extends TestCase
{
    protected function createSetting(array $attributes = []): Setting
    {
        return new Setting($attributes);
    }

    // --- get() ---

    public function test_get_returns_value_by_key()
    {
        $setting = $this->createSetting(['foo' => 'bar']);
        $this->assertSame('bar', $setting->get('foo'));
    }

    public function test_get_returns_default_when_key_missing()
    {
        $setting = $this->createSetting();
        $this->assertSame('default', $setting->get('missing', 'default'));
    }

    public function test_get_returns_null_by_default()
    {
        $setting = $this->createSetting();
        $this->assertNull($setting->get('missing'));
    }

    // --- set() ---

    public function test_set_single_key_value()
    {
        $setting = $this->createSetting();
        $setting->set('foo', 'bar');
        $this->assertSame('bar', $setting->get('foo'));
    }

    public function test_set_array_of_key_values()
    {
        $setting = $this->createSetting();
        $setting->set(['a' => 1, 'b' => 2]);
        $this->assertSame(1, $setting->get('a'));
        $this->assertSame(2, $setting->get('b'));
    }

    public function test_set_returns_self()
    {
        $setting = $this->createSetting();
        $this->assertSame($setting, $setting->set('x', 'y'));
    }

    public function test_set_dot_notation()
    {
        $setting = $this->createSetting();
        $setting->set('app.name', 'AIO');
        $this->assertSame('AIO', $setting->get('app.name'));
    }

    // --- getArray() ---

    public function test_get_array_returns_array()
    {
        $setting = $this->createSetting(['items' => json_encode(['a', 'b'])]);
        $this->assertSame(['a', 'b'], $setting->getArray('items'));
    }

    public function test_get_array_returns_default_when_missing()
    {
        $setting = $this->createSetting();
        $this->assertSame([], $setting->getArray('missing'));
    }

    public function test_get_array_returns_array_value_as_is()
    {
        $setting = $this->createSetting(['items' => ['x', 'y']]);
        $this->assertSame(['x', 'y'], $setting->getArray('items'));
    }

    public function test_get_array_returns_empty_for_falsy_value()
    {
        $setting = $this->createSetting(['empty' => '']);
        $this->assertSame([], $setting->getArray('empty'));
    }

    // --- add() ---

    public function test_add_with_key_stores_at_key()
    {
        $setting = $this->createSetting();
        $setting->add('items', 'value1', 'mykey');
        $result = $setting->getArray('items');
        $this->assertArrayHasKey('mykey', $result);
        $this->assertSame('value1', $result['mykey']);
    }

    public function test_add_without_key_appends()
    {
        $setting = $this->createSetting();
        $setting->add('items', 'first');
        $setting->add('items', 'second');
        $result = $setting->getArray('items');
        $this->assertSame(['first', 'second'], $result);
    }

    public function test_add_returns_self()
    {
        $setting = $this->createSetting();
        $this->assertSame($setting, $setting->add('items', 'val'));
    }

    public function test_add_preserves_existing_items()
    {
        $setting = $this->createSetting(['items' => ['existing']]);
        $setting->add('items', 'new');
        $result = $setting->getArray('items');
        $this->assertSame(['existing', 'new'], $result);
    }

    // --- addMany() ---

    public function test_add_many_merges_values()
    {
        $setting = $this->createSetting(['items' => ['a']]);
        $setting->addMany('items', ['b', 'c']);
        $result = $setting->getArray('items');
        $this->assertSame(['a', 'b', 'c'], $result);
    }

    public function test_add_many_on_empty_key()
    {
        $setting = $this->createSetting();
        $setting->addMany('items', ['x', 'y']);
        $result = $setting->getArray('items');
        $this->assertSame(['x', 'y'], $result);
    }

    public function test_add_many_returns_self()
    {
        $setting = $this->createSetting();
        $this->assertSame($setting, $setting->addMany('items', ['a']));
    }
}
