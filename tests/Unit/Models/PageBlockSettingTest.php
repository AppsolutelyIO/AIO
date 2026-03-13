<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlockSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class PageBlockSettingTest extends TestCase
{
    use RefreshDatabase;

    private function createSetting(array $displayOptions = [], ?string $viewStyle = null): PageBlockSetting
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Group',
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'Block',
            'class'          => 'App\\Block\\' . uniqid(),
            'scope'          => BlockScope::Page->value,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ]);

        $blockValueData = [
            'block_id'        => $blockId,
            'display_options' => $displayOptions ? json_encode($displayOptions) : null,
            'view_style'      => $viewStyle ?? 'default',
            'created_at'      => now()->toDateTimeString(),
            'updated_at'      => now()->toDateTimeString(),
        ];
        $blockValueId = DB::table('page_block_values')->insertGetId($blockValueData);

        $page = Page::factory()->create();

        $id = DB::table('page_block_settings')->insertGetId([
            'page_id'        => $page->id,
            'block_id'       => $blockId,
            'block_value_id' => $blockValueId,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'published_at'   => now()->subMinute()->toDateTimeString(),
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ]);

        return PageBlockSetting::with(['block', 'blockValue'])->find($id);
    }

    // --- getViewStyleAttribute ---

    public function test_view_style_returns_block_value_view_style(): void
    {
        $setting = $this->createSetting([], 'card');

        $this->assertEquals('card', $setting->view_style);
    }

    public function test_view_style_returns_default_when_not_set(): void
    {
        $setting = $this->createSetting([], null);

        $this->assertEquals('default', $setting->view_style);
    }

    public function test_view_style_returns_default_for_empty_string(): void
    {
        $setting = $this->createSetting([], '');

        $this->assertEquals('default', $setting->view_style);
    }

    // --- getDisplayOptionsTitleAttribute ---

    public function test_display_options_title_returns_title_from_display_options(): void
    {
        $setting = $this->createSetting(['title' => 'My Block Title']);

        $this->assertEquals('My Block Title', $setting->display_options_title);
    }

    public function test_display_options_title_returns_empty_when_no_title(): void
    {
        $setting = $this->createSetting(['color' => 'blue']);

        $this->assertEquals('', $setting->display_options_title);
    }

    public function test_display_options_title_returns_empty_when_no_display_options(): void
    {
        $setting = $this->createSetting([]);

        $this->assertEquals('', $setting->display_options_title);
    }

    // --- getDisplayOptionsValueAttribute ---

    public function test_display_options_value_returns_display_options_from_block_value(): void
    {
        $setting = $this->createSetting(['bg_color' => 'red', 'font_size' => 16]);

        $result = $setting->display_options_value;

        $this->assertIsArray($result);
        $this->assertArrayHasKey('bg_color', $result);
        $this->assertEquals('red', $result['bg_color']);
    }

    public function test_display_options_value_returns_empty_when_no_display_options(): void
    {
        $setting = $this->createSetting([]);

        $result = $setting->display_options_value;

        $this->assertIsArray($result);
    }
}
