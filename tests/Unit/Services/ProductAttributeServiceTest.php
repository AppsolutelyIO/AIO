<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\ProductAttributeService;
use Appsolutely\AIO\Tests\TestCase;

final class ProductAttributeServiceTest extends TestCase
{
    // --- attributeCacheKey (static) ---

    public function test_attribute_cache_key_returns_formatted_string(): void
    {
        $result = ProductAttributeService::attributeCacheKey('5', 'color=1&size=2');

        $this->assertIsString($result);
        $this->assertStringContainsString('attributes', $result);
        $this->assertStringContainsString('5', $result);
        $this->assertStringContainsString('color=1&size=2', $result);
    }

    public function test_attribute_cache_key_includes_app_prefix(): void
    {
        $result = ProductAttributeService::attributeCacheKey('1', 'key');

        $this->assertStringStartsWith(appsolutely() . '.', $result);
    }

    public function test_attribute_cache_key_is_unique_for_different_groups(): void
    {
        $key1 = ProductAttributeService::attributeCacheKey('1', 'same-key');
        $key2 = ProductAttributeService::attributeCacheKey('2', 'same-key');

        $this->assertNotEquals($key1, $key2);
    }

    public function test_attribute_cache_key_is_unique_for_different_keys(): void
    {
        $key1 = ProductAttributeService::attributeCacheKey('1', 'key-a');
        $key2 = ProductAttributeService::attributeCacheKey('1', 'key-b');

        $this->assertNotEquals($key1, $key2);
    }

    public function test_attribute_cache_key_is_deterministic(): void
    {
        $key1 = ProductAttributeService::attributeCacheKey('3', 'test');
        $key2 = ProductAttributeService::attributeCacheKey('3', 'test');

        $this->assertEquals($key1, $key2);
    }
}
