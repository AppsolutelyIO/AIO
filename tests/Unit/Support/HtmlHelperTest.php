<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\Helper;
use Appsolutely\AIO\Support\HtmlHelper;
use Appsolutely\AIO\Tests\Unit\TestCase;

class HtmlHelperTest extends TestCase
{
    // --- HtmlHelper::buildAttributes() ---

    public function test_build_attributes_basic()
    {
        $html = HtmlHelper::buildAttributes(['class' => 'foo', 'id' => 'bar']);
        $this->assertStringContainsString('class="foo"', $html);
        $this->assertStringContainsString('id="bar"', $html);
    }

    public function test_build_attributes_array_value()
    {
        $html = HtmlHelper::buildAttributes(['class' => ['foo', 'bar']]);
        $this->assertStringContainsString('class="foo bar"', $html);
    }

    public function test_build_attributes_numeric_key()
    {
        $html = HtmlHelper::buildAttributes(['disabled']);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    public function test_build_attributes_escapes_special_chars()
    {
        $html = HtmlHelper::buildAttributes(['title' => '<script>alert("xss")</script>']);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    // --- HtmlHelper::entityEncode() ---

    public function test_entity_encode_string()
    {
        $this->assertSame('&lt;b&gt;test&lt;/b&gt;', HtmlHelper::entityEncode('<b>test</b>'));
    }

    public function test_entity_encode_array()
    {
        $result = HtmlHelper::entityEncode(['<b>a</b>', '<i>b</i>']);
        $this->assertSame(['&lt;b&gt;a&lt;/b&gt;', '&lt;i&gt;b&lt;/i&gt;'], $result);
    }

    public function test_entity_encode_object_passthrough()
    {
        $obj = new \stdClass();
        $this->assertSame($obj, HtmlHelper::entityEncode($obj));
    }

    // --- HtmlHelper::render() ---

    public function test_render_string()
    {
        $this->assertSame('hello', HtmlHelper::render('hello'));
    }

    public function test_render_closure()
    {
        $result = HtmlHelper::render(function () {
            return 'from closure';
        });
        $this->assertSame('from closure', $result);
    }

    public function test_render_closure_with_params()
    {
        $result = HtmlHelper::render(function ($a, $b) {
            return $a . '-' . $b;
        }, ['foo', 'bar']);
        $this->assertSame('foo-bar', $result);
    }

    public function test_render_null()
    {
        $this->assertSame('', HtmlHelper::render(null));
    }

    public function test_render_integer()
    {
        $this->assertSame('42', HtmlHelper::render(42));
    }

    // --- HtmlHelper::formatElementName() ---

    public function test_format_element_name_simple()
    {
        $this->assertSame('name', HtmlHelper::formatElementName('name'));
    }

    public function test_format_element_name_dotted()
    {
        $this->assertSame('user[name]', HtmlHelper::formatElementName('user.name'));
    }

    public function test_format_element_name_deeply_nested()
    {
        $this->assertSame('user[address][city]', HtmlHelper::formatElementName('user.address.city'));
    }

    public function test_format_element_name_array_input()
    {
        $result = HtmlHelper::formatElementName(['user.name', 'user.email']);
        $this->assertSame(['user[name]', 'user[email]'], $result);
    }

    // --- Helper delegation ---

    public function test_helper_delegates_render()
    {
        $helper = Helper::render('test');
        $direct = HtmlHelper::render('test');
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_build_html_attributes()
    {
        $helper = Helper::buildHtmlAttributes(['class' => 'foo']);
        $direct = HtmlHelper::buildAttributes(['class' => 'foo']);
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_html_entity_encode()
    {
        $helper = Helper::htmlEntityEncode('<b>test</b>');
        $direct = HtmlHelper::entityEncode('<b>test</b>');
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_format_element_name()
    {
        $helper = Helper::formatElementName('user.name');
        $direct = HtmlHelper::formatElementName('user.name');
        $this->assertSame($direct, $helper);
    }
}
