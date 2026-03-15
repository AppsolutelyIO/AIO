<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\ReleaseVersion;
use Appsolutely\AIO\Repositories\ReleaseVersionRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ReleaseVersionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ReleaseVersionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ReleaseVersionRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(ReleaseVersionRepository::class, $this->repository);
    }

    public function test_model_returns_release_version_class(): void
    {
        $this->assertEquals(ReleaseVersion::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_create_stores_release_version(): void
    {
        $version = $this->repository->create([
            'version' => '1.0.0-' . uniqid(),
            'status'  => Status::ACTIVE->value,
        ]);

        $this->assertInstanceOf(ReleaseVersion::class, $version);
        $this->assertDatabaseHas('release_versions', ['id' => $version->id]);
    }

    public function test_find_returns_version_when_exists(): void
    {
        $version = ReleaseVersion::create([
            'version' => '2.0.0-' . uniqid(),
            'status'  => Status::ACTIVE->value,
        ]);

        $result = $this->repository->find($version->id);

        $this->assertInstanceOf(ReleaseVersion::class, $result);
        $this->assertEquals($version->id, $result->id);
    }

    public function test_find_by_field_returns_active_versions(): void
    {
        ReleaseVersion::create(['version' => '3.0.0', 'status' => Status::ACTIVE->value]);
        ReleaseVersion::create(['version' => '3.0.1', 'status' => Status::INACTIVE->value]);

        $result = $this->repository->findByField('status', Status::ACTIVE->value);

        $this->assertCount(1, $result);
    }
}
