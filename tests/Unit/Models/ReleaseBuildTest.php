<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\ReleaseBuild;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class ReleaseBuildTest extends TestCase
{
    use RefreshDatabase;

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
            'published_at' => now(),
        ], $attrs));
    }

    // --- getDownloadUrlAttribute ---

    public function test_download_url_returns_url_when_path_is_set(): void
    {
        $build = $this->createBuild(['path' => 'releases/app.exe']);

        $this->assertNotNull($build->download_url);
        $this->assertStringContainsString('releases/app.exe', $build->download_url);
    }

    public function test_download_url_returns_null_when_path_is_empty(): void
    {
        $build = $this->createBuild(['path' => null]);

        $this->assertNull($build->download_url);
    }

    public function test_download_url_is_string_when_path_set(): void
    {
        $build = $this->createBuild(['path' => 'builds/installer.dmg']);

        $this->assertIsString($build->download_url);
    }
}
