<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\AdminSetting;

final class AdminSettingRepository extends BaseRepository
{
    public function model(): string
    {
        return AdminSetting::class;
    }
}
