<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\ArrayHelper;
use Appsolutely\AIO\Support\Helper;
use Appsolutely\AIO\Tests\Unit\TestCase;

class ArrayHelperTest extends TestCase
{
    // --- ArrayHelper::convert() ---

    public function test_convert_returns_empty_for_null()
    {
        $this->assertSame([], ArrayHelper::convert(null));
    }

    public function test_convert_returns_empty_for_empty_string()
    {
        $this->assertSame([], ArrayHelper::convert(''));
    }

    public function test_convert_parses_comma_separated_string()
    {
        $this->assertSame(['a', 'b', 'c'], ArrayHelper::convert('a,b,c'));
    }

    public function test_convert_parses_json_string()
    {
        $this->assertSame(['foo' => 'bar'], ArrayHelper::convert('{"foo":"bar"}'));
    }

    public function test_convert_filters_empty_values_by_default()
    {
        $result = ArrayHelper::convert(['a', '', null, 'b']);
        $this->assertSame([0 => 'a', 3 => 'b'], $result);
    }

    public function test_convert_no_filter_when_disabled()
    {
        $result = ArrayHelper::convert(['a', '', null, 'b'], false);
        $this->assertSame(['a', '', null, 'b'], $result);
    }

    public function test_convert_from_closure()
    {
        $result = ArrayHelper::convert(function () {
            return [1, 2, 3];
        });
        $this->assertSame([1, 2, 3], $result);
    }

    public function test_convert_from_scalar()
    {
        $this->assertSame([42], ArrayHelper::convert(42));
    }

    // --- ArrayHelper::deleteByValue() ---

    public function test_delete_by_value()
    {
        $array = ['a', 'b', 'c', 'd'];
        ArrayHelper::deleteByValue($array, 'b');
        $this->assertSame([0 => 'a', 2 => 'c', 3 => 'd'], $array);
    }

    public function test_delete_by_value_multiple()
    {
        $array = ['a', 'b', 'c', 'd'];
        ArrayHelper::deleteByValue($array, ['a', 'c']);
        $this->assertSame([1 => 'b', 3 => 'd'], $array);
    }

    // --- ArrayHelper::deleteContains() ---

    public function test_delete_contains()
    {
        $array = ['foo-bar', 'baz-qux', 'foo-baz'];
        ArrayHelper::deleteContains($array, 'foo');
        $this->assertSame([1 => 'baz-qux'], $array);
    }

    // --- ArrayHelper::buildNested() ---

    public function test_build_nested()
    {
        $nodes = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 1, 'name' => 'Child 2'],
            ['id' => 4, 'parent_id' => 2, 'name' => 'Grandchild'],
        ];

        $tree = ArrayHelper::buildNested($nodes);

        $this->assertCount(1, $tree);
        $this->assertSame('Root', $tree[0]['name']);
        $this->assertCount(2, $tree[0]['children']);
        $this->assertSame('Grandchild', $tree[0]['children'][0]['children'][0]['name']);
    }

    public function test_build_nested_custom_keys()
    {
        $nodes = [
            ['uid' => 1, 'pid' => 0, 'title' => 'A'],
            ['uid' => 2, 'pid' => 1, 'title' => 'B'],
        ];

        $tree = ArrayHelper::buildNested($nodes, 0, 'uid', 'pid', 'items');

        $this->assertCount(1, $tree);
        $this->assertSame('B', $tree[0]['items'][0]['title']);
    }

    public function test_build_nested_empty()
    {
        $this->assertSame([], ArrayHelper::buildNested([]));
    }

    // --- ArrayHelper::export() ---

    public function test_export_simple()
    {
        $array  = ['key' => 'value'];
        $result = ArrayHelper::export($array);
        $this->assertStringContainsString("'key' => 'value'", $result);
    }

    public function test_export_booleans()
    {
        $array  = ['enabled' => true, 'disabled' => false, 'empty' => null];
        $result = ArrayHelper::export($array);
        $this->assertStringContainsString("'enabled' => true", $result);
        $this->assertStringContainsString("'disabled' => false", $result);
        $this->assertStringContainsString("'empty' => null", $result);
    }

    public function test_export_php()
    {
        $array  = ['key' => 'value'];
        $result = ArrayHelper::exportPhp($array);
        $this->assertStringStartsWith('<?php', $result);
        $this->assertStringContainsString('return [', $result);
    }

    // --- ArrayHelper::set() ---

    public function test_set_simple()
    {
        $array = [];
        ArrayHelper::set($array, 'foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $array);
    }

    public function test_set_nested()
    {
        $array = [];
        ArrayHelper::set($array, 'a.b.c', 'deep');
        $this->assertSame(['a' => ['b' => ['c' => 'deep']]], $array);
    }

    public function test_set_null_key_replaces_array()
    {
        $array  = ['old'];
        $result = ArrayHelper::set($array, null, 'replaced');
        $this->assertSame('replaced', $result);
    }

    // --- ArrayHelper::equal() ---

    public function test_equal_same_strings()
    {
        $this->assertTrue(ArrayHelper::equal('foo', 'foo'));
    }

    public function test_equal_string_and_int()
    {
        $this->assertTrue(ArrayHelper::equal(1, '1'));
    }

    public function test_equal_null_returns_false()
    {
        $this->assertFalse(ArrayHelper::equal(null, 'foo'));
    }

    // --- ArrayHelper::inArray() ---

    public function test_in_array_with_loose_type()
    {
        $this->assertTrue(ArrayHelper::inArray(1, [1, 2, 3]));
        $this->assertTrue(ArrayHelper::inArray('1', [1, 2, 3]));
    }

    public function test_in_array_not_found()
    {
        $this->assertFalse(ArrayHelper::inArray(4, [1, 2, 3]));
    }

    // --- ArrayHelper::keyExists() ---

    public function test_key_exists_array()
    {
        $this->assertTrue(ArrayHelper::keyExists('foo', ['foo' => 1]));
        $this->assertFalse(ArrayHelper::keyExists('baz', ['foo' => 1]));
    }

    // --- ArrayHelper::camel() ---

    public function test_camel()
    {
        $array = ['foo_bar' => 1, 'baz_qux' => 2];
        ArrayHelper::camel($array);
        $this->assertArrayHasKey('fooBar', $array);
        $this->assertArrayHasKey('bazQux', $array);
    }

    // --- Helper delegation ---

    public function test_helper_delegates_array()
    {
        $helper = Helper::array('a,b,c');
        $direct = ArrayHelper::convert('a,b,c');
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_equal()
    {
        $helper = Helper::equal('foo', 'foo');
        $direct = ArrayHelper::equal('foo', 'foo');
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_in_array()
    {
        $helper = Helper::inArray(1, [1, 2]);
        $direct = ArrayHelper::inArray(1, [1, 2]);
        $this->assertSame($direct, $helper);
    }
}
