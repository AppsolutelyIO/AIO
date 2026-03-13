<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Repositories\ReleaseBuildRepository;
use Appsolutely\AIO\Repositories\ReleaseVersionRepository;
use Appsolutely\AIO\Services\Contracts\ReleaseServiceInterface;

final readonly class ReleaseService implements ReleaseServiceInterface
{
    public function __construct(
        protected ReleaseVersionRepository $versionRepository,
        protected ReleaseBuildRepository $buildRepository
    ) {}

    public function getLatestBuild(?string $platform, ?string $arch): ReleaseBuild
    {
        return $this->buildRepository->getLatestBuild($platform, $arch);
    }
}
