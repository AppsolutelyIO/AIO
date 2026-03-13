<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Tests\TestCase;

final class ArticleTest extends TestCase
{
    // --- defaultSetting ---

    public function test_default_setting_returns_array(): void
    {
        $result = Article::defaultSetting();

        $this->assertIsArray($result);
    }

    public function test_default_setting_contains_meta_title_key(): void
    {
        $result = Article::defaultSetting();

        $this->assertArrayHasKey('meta_title', $result);
    }

    public function test_default_setting_meta_title_is_empty_string(): void
    {
        $result = Article::defaultSetting();

        $this->assertSame('', $result['meta_title']);
    }
}
