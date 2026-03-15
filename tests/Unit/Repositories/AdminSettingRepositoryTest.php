<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\AdminSetting;
use Appsolutely\AIO\Repositories\AdminSettingRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class AdminSettingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AdminSettingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AdminSettingRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(AdminSettingRepository::class, $this->repository);
    }

    public function test_model_returns_admin_setting_class(): void
    {
        $this->assertEquals(AdminSetting::class, $this->repository->model());
    }

    public function test_find_returns_null_for_nonexistent_id(): void
    {
        $result = AdminSetting::find(99999);

        $this->assertNull($result);
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
    }
}
