<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Repositories\ReleaseBuildRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class ReleaseBuildRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReleaseBuildRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ReleaseBuildRepository::class);
    }

    private function createVersion(): int
    {
        return DB::table('release_versions')->insertGetId([
            'version'    => '1.0.' . uniqid(),
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function createBuild(array $attrs = []): ReleaseBuild
    {
        $versionId = $attrs['version_id'] ?? $this->createVersion();

        return ReleaseBuild::create(array_merge([
            'version_id'   => $versionId,
            'platform'     => 'windows',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subMinute(),
        ], $attrs));
    }

    // --- getLatestBuild ---

    public function test_get_latest_build_returns_most_recent_active_build(): void
    {
        $this->createBuild(['published_at' => now()->subDay()]);
        $this->createBuild(['published_at' => now()->subHour()]);

        $result = $this->repository->getLatestBuild(null, null);

        $this->assertInstanceOf(ReleaseBuild::class, $result);
    }

    public function test_get_latest_build_throws_when_no_active_builds(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getLatestBuild(null, null);
    }

    public function test_get_latest_build_filters_by_platform(): void
    {
        $this->createBuild(['platform' => 'windows']);
        $this->createBuild(['platform' => 'darwin']);

        $result = $this->repository->getLatestBuild('darwin', null);

        $this->assertEquals('darwin', $result->platform->value);
    }

    public function test_get_latest_build_throws_when_platform_not_found(): void
    {
        $this->createBuild(['platform' => 'windows']);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->getLatestBuild('ios', null);
    }

    public function test_get_latest_build_filters_by_arch(): void
    {
        $this->createBuild(['arch' => 'x64']);
        $this->createBuild(['arch' => 'arm64']);

        $result = $this->repository->getLatestBuild(null, 'x64');

        $this->assertEquals('x64', $result->arch);
    }

    public function test_get_latest_build_excludes_inactive(): void
    {
        $this->createBuild(['status' => Status::INACTIVE]);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->getLatestBuild(null, null);
    }
}
