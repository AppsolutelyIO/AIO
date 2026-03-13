<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\ReleaseBuild;

interface ReleaseServiceInterface
{
    /**
     * Get latest build for platform and architecture
     */
    public function getLatestBuild(?string $platform, ?string $arch): ReleaseBuild;
}
