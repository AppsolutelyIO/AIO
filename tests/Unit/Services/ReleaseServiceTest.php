<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Services\ReleaseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class ReleaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReleaseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReleaseService::class);
    }

    private function createBuild(array $attrs = []): ReleaseBuild
    {
        $versionId = DB::table('release_versions')->insertGetId([
            'version'    => '1.0.' . uniqid(),
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return ReleaseBuild::create(array_merge([
            'version_id'   => $versionId,
            'platform'     => 'windows',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subMinute(),
        ], $attrs));
    }

    // --- getLatestBuild ---

    public function test_get_latest_build_returns_release_build(): void
    {
        $this->createBuild();

        $result = $this->service->getLatestBuild(null, null);

        $this->assertInstanceOf(ReleaseBuild::class, $result);
    }

    public function test_get_latest_build_filters_by_platform(): void
    {
        $this->createBuild(['platform' => 'linux']);
        $this->createBuild(['platform' => 'darwin']);

        $result = $this->service->getLatestBuild('linux', null);

        $this->assertEquals('linux', $result->platform->value);
    }

    public function test_get_latest_build_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getLatestBuild(null, null);
    }
}
