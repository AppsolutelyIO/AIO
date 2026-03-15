<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\Helper;
use Appsolutely\AIO\Support\UrlHelper;
use Appsolutely\AIO\Tests\Unit\TestCase;

class UrlHelperTest extends TestCase
{
    // --- UrlHelper::withQuery() ---

    public function test_with_query_adds_params()
    {
        $result = UrlHelper::withQuery('http://example.com', ['page' => 2]);
        $this->assertSame('http://example.com?page=2', $result);
    }

    public function test_with_query_merges_existing()
    {
        $result = UrlHelper::withQuery('http://example.com?foo=1', ['bar' => 2]);
        $this->assertSame('http://example.com?foo=1&bar=2', $result);
    }

    public function test_with_query_overrides_existing()
    {
        $result = UrlHelper::withQuery('http://example.com?page=1', ['page' => 3]);
        $this->assertSame('http://example.com?page=3', $result);
    }

    public function test_with_query_returns_null_for_null()
    {
        $this->assertNull(UrlHelper::withQuery(null));
    }

    public function test_with_query_returns_url_for_empty_query()
    {
        $this->assertSame('http://example.com', UrlHelper::withQuery('http://example.com', []));
    }

    // --- UrlHelper::withoutQuery() ---

    public function test_without_query_removes_param()
    {
        $result = UrlHelper::withoutQuery('http://example.com?foo=1&bar=2', 'foo');
        $this->assertSame('http://example.com?bar=2', $result);
    }

    public function test_without_query_removes_multiple_params()
    {
        $result = UrlHelper::withoutQuery('http://example.com?a=1&b=2&c=3', ['a', 'c']);
        $this->assertSame('http://example.com?b=2', $result);
    }

    public function test_without_query_returns_base_when_all_removed()
    {
        $result = UrlHelper::withoutQuery('http://example.com?foo=1', 'foo');
        $this->assertSame('http://example.com', $result);
    }

    public function test_without_query_no_change_without_query_string()
    {
        $result = UrlHelper::withoutQuery('http://example.com', 'foo');
        $this->assertSame('http://example.com', $result);
    }

    // --- UrlHelper::hasQuery() ---

    public function test_has_query_returns_true()
    {
        $this->assertTrue(UrlHelper::hasQuery('http://example.com?page=1', 'page'));
    }

    public function test_has_query_returns_false()
    {
        $this->assertFalse(UrlHelper::hasQuery('http://example.com?page=1', 'sort'));
    }

    public function test_has_query_returns_false_without_query()
    {
        $this->assertFalse(UrlHelper::hasQuery('http://example.com', 'page'));
    }

    // --- Helper delegation ---

    public function test_helper_delegates_url_with_query()
    {
        $helper = Helper::urlWithQuery('http://example.com', ['page' => 2]);
        $direct = UrlHelper::withQuery('http://example.com', ['page' => 2]);
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_url_without_query()
    {
        $helper = Helper::urlWithoutQuery('http://example.com?foo=1&bar=2', 'foo');
        $direct = UrlHelper::withoutQuery('http://example.com?foo=1&bar=2', 'foo');
        $this->assertSame($direct, $helper);
    }

    public function test_helper_delegates_url_has_query()
    {
        $helper = Helper::urlHasQuery('http://example.com?page=1', 'page');
        $direct = UrlHelper::hasQuery('http://example.com?page=1', 'page');
        $this->assertSame($direct, $helper);
    }
}
