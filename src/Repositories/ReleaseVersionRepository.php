<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ReleaseVersion;

final class ReleaseVersionRepository extends BaseRepository
{
    public function model(): string
    {
        return ReleaseVersion::class;
    }
}
