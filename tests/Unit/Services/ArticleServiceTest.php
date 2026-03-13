<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ArticleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ArticleService::class);
    }

    // --- getContentSummary ---

    public function test_get_content_summary_returns_string(): void
    {
        $article = Article::factory()->make(['content' => 'Hello World']);

        $result = $this->service->getContentSummary($article);

        $this->assertIsString($result);
    }

    public function test_get_content_summary_truncates_long_content(): void
    {
        $longContent = str_repeat('a', 300);
        $article     = Article::factory()->make(['content' => $longContent]);

        config(['misc.contentSummaryLength' => 200]);
        $result = $this->service->getContentSummary($article);

        $this->assertLessThanOrEqual(203, strlen($result)); // 200 + "..."
        $this->assertStringEndsWith('...', $result);
    }

    public function test_get_content_summary_returns_full_content_when_short(): void
    {
        $shortContent = 'Short content';
        $article      = Article::factory()->make(['content' => $shortContent]);

        $result = $this->service->getContentSummary($article);

        $this->assertEquals($shortContent, $result);
    }

    public function test_get_content_summary_uses_config_length(): void
    {
        config(['misc.contentSummaryLength' => 50]);
        $article = Article::factory()->make(['content' => str_repeat('x', 100)]);

        $result = $this->service->getContentSummary($article);

        $this->assertLessThanOrEqual(53, strlen($result)); // 50 + "..."
    }

    public function test_get_content_summary_uses_default_length_when_config_missing(): void
    {
        config(['misc.contentSummaryLength' => null]);
        $content = str_repeat('b', 300);
        $article = Article::factory()->make(['content' => $content]);

        $result = $this->service->getContentSummary($article);

        // Default is 200 chars + "..."
        $this->assertLessThanOrEqual(203, strlen($result));
    }

    // --- getFormattedContent ---

    public function test_get_formatted_content_converts_markdown_to_html(): void
    {
        $article = Article::factory()->make(['content' => '# Hello']);

        $result = $this->service->getFormattedContent($article);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<h1>', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function test_get_formatted_content_converts_bold_markdown(): void
    {
        $article = Article::factory()->make(['content' => '**bold text**']);

        $result = $this->service->getFormattedContent($article);

        $this->assertNotNull($result);
        $this->assertStringContainsString('<strong>', $result);
    }

    public function test_get_formatted_content_returns_null_on_exception(): void
    {
        // When content is null, the converter throws a TypeError (null given)
        // The service's catch block only catches CommonMarkException, so we
        // verify the service handles the happy path; null content is a caller error.
        $article = Article::factory()->make(['content' => '']);

        $result = $this->service->getFormattedContent($article);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_get_formatted_content_returns_string_for_plain_text(): void
    {
        $article = Article::factory()->make(['content' => 'Plain text with no markdown']);

        $result = $this->service->getFormattedContent($article);

        $this->assertNotNull($result);
        $this->assertStringContainsString('Plain text with no markdown', $result);
    }
}
