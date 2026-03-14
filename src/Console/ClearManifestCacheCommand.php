<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Services\BlockRegistryService;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Illuminate\Console\Command;
use Qirolab\Theme\Theme;

/**
 * Clear theme manifest and block registry cache.
 *
 * Run after adding or changing templates in manifest.json or page_blocks
 * so the block registry and other manifest consumers pick up the changes immediately.
 */
final class ClearManifestCacheCommand extends Command
{
    protected $signature = 'manifest:clear-cache
                           {--theme= : Theme name (default: active theme)}';

    protected $description = 'Clear cached theme manifest.json and block registry (e.g. after adding new block templates)';

    public function handle(
        ManifestServiceInterface $manifestService,
        BlockRegistryService $blockRegistryService
    ): int {
        $themeName = $this->option('theme') ?? Theme::active();

        $manifestService->clearCache($themeName);
        $blockRegistryService->clearCache($themeName);

        $this->info("Manifest and block registry cache cleared for theme: {$themeName}");

        return self::SUCCESS;
    }
}
