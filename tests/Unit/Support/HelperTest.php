<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\Helper;
use Appsolutely\AIO\Tests\Unit\TestCase;

class HelperTest extends TestCase
{
    // --- Helper::array() ---

    public function test_array_returns_empty_for_null()
    {
        $this->assertSame([], Helper::array(null));
    }

    public function test_array_returns_empty_for_empty_string()
    {
        $this->assertSame([], Helper::array(''));
    }

    public function test_array_returns_empty_for_empty_array()
    {
        $this->assertSame([], Helper::array([]));
    }

    public function test_array_parses_comma_separated_string()
    {
        $this->assertSame(['a', 'b', 'c'], Helper::array('a,b,c'));
    }

    public function test_array_parses_json_string()
    {
        $this->assertSame(['foo' => 'bar'], Helper::array('{"foo":"bar"}'));
    }

    public function test_array_falls_back_to_comma_split_on_invalid_json()
    {
        $this->assertSame(['not', '{json}'], Helper::array('not,{json}'));
    }

    public function test_array_filters_empty_values_by_default()
    {
        $result = Helper::array(['a', '', null, 'b']);
        $this->assertSame([0 => 'a', 3 => 'b'], $result);
    }

    public function test_array_no_filter_when_disabled()
    {
        $result = Helper::array(['a', '', null, 'b'], false);
        $this->assertSame(['a', '', null, 'b'], $result);
    }

    public function test_array_from_closure()
    {
        $result = Helper::array(function () {
            return [1, 2, 3];
        });
        $this->assertSame([1, 2, 3], $result);
    }

    public function test_array_from_scalar()
    {
        $this->assertSame([42], Helper::array(42));
    }

    // --- Helper::slug() ---

    public function test_slug_converts_camel_case()
    {
        $this->assertSame('hello-world', Helper::slug('HelloWorld'));
    }

    public function test_slug_converts_with_custom_symbol()
    {
        $this->assertSame('hello_world', Helper::slug('HelloWorld', '_'));
    }

    public function test_slug_converts_underscores()
    {
        $this->assertSame('foo-bar', Helper::slug('foo_bar'));
    }

    public function test_slug_mixed_camel_and_underscores()
    {
        $this->assertSame('my-foo-bar', Helper::slug('myFoo_bar'));
    }

    // --- Helper::buildHtmlAttributes() ---

    public function test_build_html_attributes_basic()
    {
        $html = Helper::buildHtmlAttributes(['class' => 'foo', 'id' => 'bar']);
        $this->assertStringContainsString('class="foo"', $html);
        $this->assertStringContainsString('id="bar"', $html);
    }

    public function test_build_html_attributes_array_value()
    {
        $html = Helper::buildHtmlAttributes(['class' => ['foo', 'bar']]);
        $this->assertStringContainsString('class="foo bar"', $html);
    }

    public function test_build_html_attributes_numeric_key()
    {
        $html = Helper::buildHtmlAttributes(['disabled']);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    public function test_build_html_attributes_escapes_special_chars()
    {
        $html = Helper::buildHtmlAttributes(['title' => '<script>alert("xss")</script>']);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // --- Helper::urlWithQuery() ---

    public function test_url_with_query_adds_params()
    {
        $result = Helper::urlWithQuery('http://example.com', ['page' => 2]);
        $this->assertSame('http://example.com?page=2', $result);
    }

    public function test_url_with_query_merges_existing()
    {
        $result = Helper::urlWithQuery('http://example.com?foo=1', ['bar' => 2]);
        $this->assertSame('http://example.com?foo=1&bar=2', $result);
    }

    public function test_url_with_query_overrides_existing()
    {
        $result = Helper::urlWithQuery('http://example.com?page=1', ['page' => 3]);
        $this->assertSame('http://example.com?page=3', $result);
    }

    public function test_url_with_query_returns_null_for_null()
    {
        $this->assertNull(Helper::urlWithQuery(null));
    }

    public function test_url_with_query_returns_url_for_empty_query()
    {
        $this->assertSame('http://example.com', Helper::urlWithQuery('http://example.com', []));
    }

    // --- Helper::urlWithoutQuery() ---

    public function test_url_without_query_removes_param()
    {
        $result = Helper::urlWithoutQuery('http://example.com?foo=1&bar=2', 'foo');
        $this->assertSame('http://example.com?bar=2', $result);
    }

    public function test_url_without_query_removes_multiple_params()
    {
        $result = Helper::urlWithoutQuery('http://example.com?a=1&b=2&c=3', ['a', 'c']);
        $this->assertSame('http://example.com?b=2', $result);
    }

    public function test_url_without_query_returns_base_when_all_removed()
    {
        $result = Helper::urlWithoutQuery('http://example.com?foo=1', 'foo');
        $this->assertSame('http://example.com', $result);
    }

    public function test_url_without_query_no_change_without_query_string()
    {
        $result = Helper::urlWithoutQuery('http://example.com', 'foo');
        $this->assertSame('http://example.com', $result);
    }

    // --- Helper::urlHasQuery() ---

    public function test_url_has_query_returns_true()
    {
        $this->assertTrue(Helper::urlHasQuery('http://example.com?page=1', 'page'));
    }

    public function test_url_has_query_returns_false()
    {
        $this->assertFalse(Helper::urlHasQuery('http://example.com?page=1', 'sort'));
    }

    public function test_url_has_query_returns_false_without_query()
    {
        $this->assertFalse(Helper::urlHasQuery('http://example.com', 'page'));
    }

    // --- Helper::buildNestedArray() ---

    public function test_build_nested_array()
    {
        $nodes = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
            ['id' => 3, 'parent_id' => 1, 'name' => 'Child 2'],
            ['id' => 4, 'parent_id' => 2, 'name' => 'Grandchild'],
        ];

        $tree = Helper::buildNestedArray($nodes);

        $this->assertCount(1, $tree);
        $this->assertSame('Root', $tree[0]['name']);
        $this->assertCount(2, $tree[0]['children']);
        $this->assertSame('Grandchild', $tree[0]['children'][0]['children'][0]['name']);
    }

    public function test_build_nested_array_custom_keys()
    {
        $nodes = [
            ['uid' => 1, 'pid' => 0, 'title' => 'A'],
            ['uid' => 2, 'pid' => 1, 'title' => 'B'],
        ];

        $tree = Helper::buildNestedArray($nodes, 0, 'uid', 'pid', 'items');

        $this->assertCount(1, $tree);
        $this->assertSame('B', $tree[0]['items'][0]['title']);
    }

    public function test_build_nested_array_empty()
    {
        $this->assertSame([], Helper::buildNestedArray([]));
    }

    // --- Helper::equal() ---

    public function test_equal_same_strings()
    {
        $this->assertTrue(Helper::equal('foo', 'foo'));
    }

    public function test_equal_string_and_int()
    {
        $this->assertTrue(Helper::equal(1, '1'));
    }

    public function test_equal_different_values()
    {
        $this->assertFalse(Helper::equal('foo', 'bar'));
    }

    public function test_equal_null_returns_false()
    {
        $this->assertFalse(Helper::equal(null, 'foo'));
        $this->assertFalse(Helper::equal('foo', null));
        $this->assertFalse(Helper::equal(null, null));
    }

    public function test_equal_non_scalar()
    {
        $obj = new \stdClass();
        $this->assertTrue(Helper::equal($obj, $obj));
        $this->assertFalse(Helper::equal(new \stdClass(), new \stdClass()));
    }

    // --- Helper::inArray() ---

    public function test_in_array_with_loose_type()
    {
        $this->assertTrue(Helper::inArray(1, [1, 2, 3]));
        $this->assertTrue(Helper::inArray('1', [1, 2, 3]));
        $this->assertTrue(Helper::inArray(2, ['1', '2', '3']));
    }

    public function test_in_array_not_found()
    {
        $this->assertFalse(Helper::inArray(4, [1, 2, 3]));
    }

    // --- Helper::strLimit() ---

    public function test_str_limit_short_string()
    {
        $this->assertSame('hello', Helper::strLimit('hello', 10));
    }

    public function test_str_limit_long_string()
    {
        $this->assertSame('hel...', Helper::strLimit('hello world', 3));
    }

    public function test_str_limit_custom_end()
    {
        $this->assertSame('hel>>>', Helper::strLimit('hello world', 3, '>>>'));
    }

    public function test_str_limit_unicode()
    {
        $this->assertSame('你好...', Helper::strLimit('你好世界测试', 2));
    }

    // --- Helper::deleteByValue() ---

    public function test_delete_by_value()
    {
        $array = ['a', 'b', 'c', 'd'];
        Helper::deleteByValue($array, 'b');
        $this->assertSame([0 => 'a', 2 => 'c', 3 => 'd'], $array);
    }

    public function test_delete_by_value_multiple()
    {
        $array = ['a', 'b', 'c', 'd'];
        Helper::deleteByValue($array, ['a', 'c']);
        $this->assertSame([1 => 'b', 3 => 'd'], $array);
    }

    // --- Helper::deleteContains() ---

    public function test_delete_contains()
    {
        $array = ['foo-bar', 'baz-qux', 'foo-baz'];
        Helper::deleteContains($array, 'foo');
        $this->assertSame([1 => 'baz-qux'], $array);
    }

    // --- Helper::validateExtensionName() ---

    public function test_validate_extension_name_valid()
    {
        $this->assertEquals(1, Helper::validateExtensionName('vendor/package'));
        $this->assertEquals(1, Helper::validateExtensionName('my-vendor/my_package'));
    }

    public function test_validate_extension_name_invalid()
    {
        $this->assertEquals(0, Helper::validateExtensionName('invalid'));
        $this->assertEquals(0, Helper::validateExtensionName('vendor/'));
        $this->assertEquals(0, Helper::validateExtensionName('/package'));
    }

    // --- Helper::colorLighten() / colorDarken() ---

    public function test_color_lighten()
    {
        $result = Helper::colorLighten('#333333', 10);
        $this->assertStringStartsWith('#', $result);
        $this->assertNotSame('#333333', $result);
    }

    public function test_color_lighten_zero_returns_same()
    {
        $this->assertSame('#333333', Helper::colorLighten('#333333', 0));
    }

    public function test_color_darken()
    {
        $result = Helper::colorDarken('#cccccc', 10);
        $this->assertStringStartsWith('#', $result);
    }

    // --- Helper::colorAlpha() ---

    public function test_color_alpha()
    {
        $result = Helper::colorAlpha('#3085d6', 0.5);
        $this->assertStringContainsString('rgba(', $result);
        $this->assertStringContainsString('0.5', $result);
    }

    public function test_color_alpha_full_opacity_returns_original()
    {
        $this->assertSame('#3085d6', Helper::colorAlpha('#3085d6', 1));
    }

    // --- Helper::colorToRBG() ---

    public function test_color_to_rgb()
    {
        // #ff0000 => red=255, blue=0, green=0
        [$r, $b, $g] = Helper::colorToRBG('ff0000');
        $this->assertSame(255, $r);
        $this->assertSame(0, $b);
        $this->assertSame(0, $g);
    }

    public function test_color_to_rgb_with_amount()
    {
        [$r, $b, $g] = Helper::colorToRBG('000000', 10);
        $this->assertSame(10, $r);
        $this->assertSame(10, $b);
        $this->assertSame(10, $g);
    }

    public function test_color_to_rgb_clamps_values()
    {
        // ffffff + 10 should clamp to 255
        [$r, $b, $g] = Helper::colorToRBG('ffffff', 10);
        $this->assertSame(255, $r);
        $this->assertSame(255, $b);
        $this->assertSame(255, $g);

        // 000000 - 10 should clamp to 0
        [$r, $b, $g] = Helper::colorToRBG('000000', -10);
        $this->assertSame(0, $r);
        $this->assertSame(0, $b);
        $this->assertSame(0, $g);
    }

    // --- Helper::htmlEntityEncode() ---

    public function test_html_entity_encode_string()
    {
        $this->assertSame('&lt;b&gt;test&lt;/b&gt;', Helper::htmlEntityEncode('<b>test</b>'));
    }

    public function test_html_entity_encode_array()
    {
        $result = Helper::htmlEntityEncode(['<b>a</b>', '<i>b</i>']);
        $this->assertSame(['&lt;b&gt;a&lt;/b&gt;', '&lt;i&gt;b&lt;/i&gt;'], $result);
    }

    public function test_html_entity_encode_object_passthrough()
    {
        $obj = new \stdClass();
        $this->assertSame($obj, Helper::htmlEntityEncode($obj));
    }

    // --- Helper::formatElementName() ---

    public function test_format_element_name_simple()
    {
        $this->assertSame('name', Helper::formatElementName('name'));
    }

    public function test_format_element_name_dotted()
    {
        $this->assertSame('user[name]', Helper::formatElementName('user.name'));
    }

    public function test_format_element_name_deeply_nested()
    {
        $this->assertSame('user[address][city]', Helper::formatElementName('user.address.city'));
    }

    public function test_format_element_name_array_input()
    {
        $result = Helper::formatElementName(['user.name', 'user.email']);
        $this->assertSame(['user[name]', 'user[email]'], $result);
    }

    // --- Helper::basename() ---

    public function test_basename()
    {
        $this->assertSame('file.php', Helper::basename('path/to/file.php'));
    }

    public function test_basename_no_path()
    {
        $this->assertSame('file.php', Helper::basename('file.php'));
    }

    public function test_basename_empty()
    {
        $this->assertSame('', Helper::basename(''));
    }

    // --- Helper::keyExists() ---

    public function test_key_exists_array()
    {
        $this->assertTrue(Helper::keyExists('foo', ['foo' => 1, 'bar' => 2]));
        $this->assertFalse(Helper::keyExists('baz', ['foo' => 1]));
    }

    // --- Helper::exportArray() ---

    public function test_export_array_simple()
    {
        $array  = ['key' => 'value'];
        $result = Helper::exportArray($array);
        $this->assertStringContainsString("'key' => 'value'", $result);
    }

    public function test_export_array_booleans()
    {
        $array  = ['enabled' => true, 'disabled' => false, 'empty' => null];
        $result = Helper::exportArray($array);
        $this->assertStringContainsString("'enabled' => true", $result);
        $this->assertStringContainsString("'disabled' => false", $result);
        $this->assertStringContainsString("'empty' => null", $result);
    }

    public function test_export_array_php()
    {
        $array  = ['key' => 'value'];
        $result = Helper::exportArrayPhp($array);
        $this->assertStringStartsWith('<?php', $result);
        $this->assertStringContainsString('return [', $result);
    }

    // --- Helper::arraySet() ---

    public function test_array_set_simple()
    {
        $array = [];
        Helper::arraySet($array, 'foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $array);
    }

    public function test_array_set_nested()
    {
        $array = [];
        Helper::arraySet($array, 'a.b.c', 'deep');
        $this->assertSame(['a' => ['b' => ['c' => 'deep']]], $array);
    }

    public function test_array_set_null_key_replaces_array()
    {
        $array  = ['old'];
        $result = Helper::arraySet($array, null, 'replaced');
        $this->assertSame('replaced', $result);
    }

    // --- Helper::isIEBrowser() ---

    public function test_is_ie_browser_detects_ie()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko';
        $this->assertTrue(Helper::isIEBrowser());
    }

    public function test_is_ie_browser_returns_false_for_chrome()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $this->assertFalse(Helper::isIEBrowser());
    }

    public function test_is_ie_browser_handles_missing_user_agent()
    {
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->assertFalse(Helper::isIEBrowser());
    }

    // --- Helper::isQQBrowser() ---

    public function test_is_qq_browser_detects()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 QQBrowser/10.0';
        $this->assertTrue(Helper::isQQBrowser());
    }

    public function test_is_qq_browser_returns_false()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Chrome/90.0';
        $this->assertFalse(Helper::isQQBrowser());
    }

    // --- Helper::render() ---

    public function test_render_string()
    {
        $this->assertSame('hello', Helper::render('hello'));
    }

    public function test_render_closure()
    {
        $result = Helper::render(function () {
            return 'from closure';
        });
        $this->assertSame('from closure', $result);
    }

    public function test_render_closure_with_params()
    {
        $result = Helper::render(function ($a, $b) {
            return $a . '-' . $b;
        }, ['foo', 'bar']);
        $this->assertSame('foo-bar', $result);
    }

    public function test_render_null()
    {
        $this->assertSame('', Helper::render(null));
    }

    public function test_render_integer()
    {
        $this->assertSame('42', Helper::render(42));
    }
}
