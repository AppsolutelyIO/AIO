<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Api;

use Appsolutely\AIO\Services\ReleaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReleaseController extends BaseApiController
{
    public function __construct(
        protected ReleaseService $appService
    ) {}

    public function latest(Request $request): JsonResponse
    {
        $platform       = $request->query('platform');
        $arch           = $request->query('arch');
        $currentVersion = $request->query('current_version');

        $build = $this->appService->getLatestBuild($platform, $arch);

        if (! $build || ($currentVersion && version_compare($build->version->version, $currentVersion, '<='))) {
            return $this->flattenJson([], 204);
        }

        return $this->flattenJson([
            'version'   => $build->version->version,
            'notes'     => $build->release_notes,
            'pub_date'  => $build->published_at?->toIso8601String(),
            'platforms' => [
                ($build->platform ?? 'any') . '-' . ($build->arch ?? 'any') => [
                    'signature' => $build->signature,
                    'url'       => $build->download_url,
                ],
            ],
        ]);
    }
}
