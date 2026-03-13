<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\RouteRestrictionService;
use Appsolutely\AIO\Tests\TestCase;

final class RouteRestrictionServiceTest extends TestCase
{
    private RouteRestrictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RouteRestrictionService();
    }

    // --- getDisabledPrefixes ---

    public function test_get_disabled_prefixes_returns_empty_array_when_not_configured(): void
    {
        config(['appsolutely.features.disabled' => null]);

        $result = $this->service->getDisabledPrefixes();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_disabled_prefixes_returns_array_from_array_config(): void
    {
        config(['appsolutely.features.disabled' => ['shop', 'blog']]);

        $result = $this->service->getDisabledPrefixes();

        $this->assertEquals(['shop', 'blog'], $result);
    }

    public function test_get_disabled_prefixes_parses_comma_separated_string(): void
    {
        config(['appsolutely.features.disabled' => 'shop, blog, forum']);

        $result = $this->service->getDisabledPrefixes();

        $this->assertContains('shop', $result);
        $this->assertContains('blog', $result);
        $this->assertContains('forum', $result);
        $this->assertCount(3, $result);
    }

    public function test_get_disabled_prefixes_trims_whitespace_from_string(): void
    {
        config(['appsolutely.features.disabled' => '  shop ,  blog  ']);

        $result = $this->service->getDisabledPrefixes();

        $this->assertContains('shop', $result);
        $this->assertContains('blog', $result);
    }

    public function test_get_disabled_prefixes_returns_empty_for_non_array_non_string(): void
    {
        config(['appsolutely.features.disabled' => 42]);

        $result = $this->service->getDisabledPrefixes();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_disabled_prefixes_returns_re_indexed_array(): void
    {
        config(['appsolutely.features.disabled' => ['shop', 'blog']]);

        $result = $this->service->getDisabledPrefixes();

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
    }

    // --- isPrefixDisabled ---

    public function test_is_prefix_disabled_returns_true_for_disabled_prefix(): void
    {
        config(['appsolutely.features.disabled' => ['shop', 'blog']]);

        $this->assertTrue($this->service->isPrefixDisabled('shop'));
        $this->assertTrue($this->service->isPrefixDisabled('blog'));
    }

    public function test_is_prefix_disabled_returns_false_for_enabled_prefix(): void
    {
        config(['appsolutely.features.disabled' => ['shop']]);

        $this->assertFalse($this->service->isPrefixDisabled('blog'));
        $this->assertFalse($this->service->isPrefixDisabled('forum'));
    }

    public function test_is_prefix_disabled_returns_false_when_no_prefixes_configured(): void
    {
        config(['appsolutely.features.disabled' => []]);

        $this->assertFalse($this->service->isPrefixDisabled('shop'));
    }

    public function test_is_prefix_disabled_is_case_sensitive(): void
    {
        config(['appsolutely.features.disabled' => ['Shop']]);

        $this->assertFalse($this->service->isPrefixDisabled('shop'));
        $this->assertTrue($this->service->isPrefixDisabled('Shop'));
    }

    public function test_is_prefix_disabled_works_with_comma_string_config(): void
    {
        config(['appsolutely.features.disabled' => 'shop, blog']);

        $this->assertTrue($this->service->isPrefixDisabled('shop'));
        $this->assertTrue($this->service->isPrefixDisabled('blog'));
        $this->assertFalse($this->service->isPrefixDisabled('forum'));
    }
}
