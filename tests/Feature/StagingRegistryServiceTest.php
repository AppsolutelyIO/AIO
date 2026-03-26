<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Services\StagingRegistryService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class StagingRegistryServiceTest extends TestCase
{
    private StagingRegistryService $registry;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'aio.staging_access_enabled' => true,
            'app.url'                    => 'https://staging.example.com',
            'app.name'                   => 'Test Staging',
        ]);

        $this->registry = new StagingRegistryService();

        Redis::del('aio:staging_registry');
    }

    protected function tearDown(): void
    {
        Redis::del('aio:staging_registry');

        parent::tearDown();
    }

    public function test_heartbeat_registers_current_environment(): void
    {
        $this->registry->heartbeat();

        $entries = $this->registry->list();

        $this->assertCount(1, $entries);
        $this->assertEquals('https://staging.example.com', $entries[0]['url']);
        $this->assertEquals('Test Staging', $entries[0]['name']);
        $this->assertStringContainsString('?token=', $entries[0]['access_url']);
    }

    public function test_heartbeat_updates_last_seen(): void
    {
        Carbon::setTestNow('2026-03-26 10:00:00');
        $this->registry->heartbeat();

        Carbon::setTestNow('2026-03-26 10:15:00');
        $this->registry->heartbeat();

        $entries = $this->registry->list();

        $this->assertCount(1, $entries);
        $this->assertStringContainsString('10:15', $entries[0]['last_seen']);

        Carbon::setTestNow();
    }

    public function test_deregister_removes_current_environment(): void
    {
        $this->registry->heartbeat();
        $this->assertCount(1, $this->registry->list());

        $this->registry->deregister();
        $this->assertCount(0, $this->registry->list());
    }

    public function test_list_returns_multiple_environments(): void
    {
        $this->registry->heartbeat();

        Redis::hset('aio:staging_registry', 'https://pr-42.staging.example.com', json_encode([
            'name'      => 'PR #42',
            'last_seen' => Carbon::now()->toIso8601String(),
        ]));

        $entries = $this->registry->list();

        $this->assertCount(2, $entries);
    }

    public function test_cleanup_removes_stale_entries(): void
    {
        $this->registry->heartbeat();

        Redis::hset('aio:staging_registry', 'https://stale.example.com', json_encode([
            'name'      => 'Stale',
            'last_seen' => Carbon::now()->subHours(3)->toIso8601String(),
        ]));

        $removed = $this->registry->cleanup();

        $this->assertEquals(1, $removed);
        $this->assertCount(1, $this->registry->list());
        $this->assertEquals('https://staging.example.com', $this->registry->list()[0]['url']);
    }

    public function test_cleanup_removes_entries_with_invalid_data(): void
    {
        Redis::hset('aio:staging_registry', 'https://broken.example.com', 'not-valid-json');

        $removed = $this->registry->cleanup();

        $this->assertEquals(1, $removed);
        $this->assertCount(0, $this->registry->list());
    }

    public function test_heartbeat_skips_when_no_url_configured(): void
    {
        config(['app.url' => null]);

        $this->registry->heartbeat();

        $this->assertCount(0, $this->registry->list());
    }
}
