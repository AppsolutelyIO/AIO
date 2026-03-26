<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Models\PageBlockGroup;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Services\BlockOptionService;
use Appsolutely\AIO\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class BlockOptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private BlockOptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BlockOptionService::class);
    }

    // --- getOptionsBySettingId ---

    public function test_get_options_by_setting_id_throws_for_nonexistent_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getOptionsBySettingId(99999);
    }

    // --- getOptionsByReference ---

    public function test_get_options_by_reference_returns_null_for_nonexistent_reference(): void
    {
        $result = $this->service->getOptionsByReference('nonexistent-reference-xyz');

        $this->assertNull($result);
    }

    public function test_get_options_by_reference_returns_null_for_empty_reference(): void
    {
        $result = $this->service->getOptionsByReference('');

        $this->assertNull($result);
    }

    // --- getOptionsByBlockIdAndType ---

    public function test_get_options_by_block_id_and_type_throws_when_block_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getOptionsByBlockIdAndType(99999, 'some-type');
    }

    // --- saveOptions ---

    public function test_save_options_returns_false_when_reference_not_found(): void
    {
        $result = $this->service->saveOptions('nonexistent-reference', [], []);

        $this->assertFalse($result);
    }

    // --- saveOptions: schedule date timezone conversion ---

    public function test_save_options_converts_schedule_dates_to_utc(): void
    {
        [$setting] = $this->createBlockSettingWithValue();

        $result = $this->service->saveOptions(
            $setting->reference,
            ['title' => 'Test'],
            [],
            '2026-03-26T14:00:00+13:00',  // NZDT
            '2026-03-27T14:00:00+13:00',
        );

        $this->assertTrue($result);

        $blockValue = PageBlockValue::find($setting->block_value_id);
        // 14:00 +13:00 = 01:00 UTC
        $this->assertEquals('2026-03-26 01:00:00', $blockValue->published_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-03-27 01:00:00', $blockValue->expired_at->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $blockValue->published_at->timezoneName);
    }

    public function test_save_options_handles_utc_plus_eight_offset(): void
    {
        [$setting] = $this->createBlockSettingWithValue();

        $this->service->saveOptions(
            $setting->reference,
            ['title' => 'Test'],
            [],
            '2026-03-26T14:00:00+08:00',
        );

        $blockValue = PageBlockValue::find($setting->block_value_id);
        // 14:00 +08:00 = 06:00 UTC
        $this->assertEquals('2026-03-26 06:00:00', $blockValue->published_at->format('Y-m-d H:i:s'));
    }

    public function test_save_options_clears_schedule_dates_when_null(): void
    {
        [$setting] = $this->createBlockSettingWithValue();

        // First save with dates
        $this->service->saveOptions(
            $setting->reference,
            ['title' => 'Test'],
            [],
            '2026-03-26T14:00:00+08:00',
        );

        // Then clear
        $this->service->saveOptions(
            $setting->reference,
            ['title' => 'Test'],
            [],
            null,
            null,
        );

        $blockValue = PageBlockValue::find($setting->block_value_id);
        $this->assertNull($blockValue->published_at);
        $this->assertNull($blockValue->expired_at);
    }

    public function test_save_options_roundtrip_preserves_correct_utc_time(): void
    {
        [$setting] = $this->createBlockSettingWithValue();

        $this->service->saveOptions(
            $setting->reference,
            ['title' => 'Test'],
            [],
            '2026-03-26T14:00:00+08:00',
        );

        // Reload from DB and verify the ISO string represents the correct UTC time
        $blockValue = PageBlockValue::find($setting->block_value_id);
        $iso        = $blockValue->published_at->toIso8601String();
        $parsed     = Carbon::parse($iso);
        $this->assertEquals('2026-03-26 06:00:00', $parsed->utc()->format('Y-m-d H:i:s'));
    }

    /**
     * Create a minimal PageBlockSetting with associated BlockValue for testing.
     *
     * @return array{0: PageBlockSetting, 1: PageBlockValue}
     */
    private function createBlockSettingWithValue(): array
    {
        $group = PageBlockGroup::create([
            'title'  => 'Test Group',
            'status' => 1,
        ]);

        $block = PageBlock::create([
            'block_group_id' => $group->id,
            'title'          => 'Test Block',
            'reference'      => 'test-block',
            'class'          => 'GeneralBlock',
            'status'         => 1,
        ]);

        $blockValue = PageBlockValue::create([
            'block_id'        => $block->id,
            'view'            => 'test-block',
            'display_options' => [],
            'query_options'   => [],
        ]);

        $page = Page::factory()->published()->create();

        $setting = PageBlockSetting::create([
            'page_id'        => $page->id,
            'block_id'       => $block->id,
            'block_value_id' => $blockValue->id,
            'reference'      => 'test-ref-' . uniqid(),
            'type'           => 'test-block',
            'status'         => 1,
            'published_at'   => now(),
        ]);

        return [$setting, $blockValue];
    }
}
