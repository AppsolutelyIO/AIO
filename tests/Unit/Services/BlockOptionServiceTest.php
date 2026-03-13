<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\BlockOptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

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
}
