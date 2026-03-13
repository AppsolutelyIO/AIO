<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class GeneralPageTest extends TestCase
{
    use RefreshDatabase;

    // --- blocks() method ---

    public function test_blocks_throws_bad_method_call_when_content_has_no_blocks_method(): void
    {
        $content = new class() extends Model
        {
            protected $table = 'pages';
        };

        $generalPage = new GeneralPage($content);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('does not have a blocks() relationship');

        $generalPage->blocks();
    }

    public function test_blocks_delegates_to_parent_page_when_nested(): void
    {
        $parentPage = Page::factory()->create();

        $content     = new class() extends Model
        {
            protected $table = 'pages';
        };
        $generalPage = new GeneralPage($content, $parentPage, 'child-slug');

        $blocks = $generalPage->blocks();

        $this->assertNotNull($blocks);
    }

    // --- getMetaTitle null safety ---

    public function test_meta_title_handles_nested_page_with_null_parent_title(): void
    {
        $parentPage = Page::factory()->create(['title' => null]);

        $content = new class() extends Model
        {
            protected $table = 'pages';

            protected $attributes = ['title' => 'Child Title'];
        };

        $generalPage = new GeneralPage($content, $parentPage, 'child-slug');

        // Should not throw - the null parent title is handled gracefully
        $result = $generalPage->toArray();
        $this->assertArrayHasKey('meta_title', $result);
    }
}
