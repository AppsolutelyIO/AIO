<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface ManifestServiceInterface
{
    /**
     * Get display options from manifest.json for a specific block reference
     *
     * @param  string  $blockReference  The block reference (template key)
     * @param  string|null  $themeName  Optional theme name, defaults to active theme
     * @return array Empty array if not found
     */
    public function getDisplayOptions(string $blockReference, ?string $themeName = null): array;

    /**
     * Get full template configuration from manifest.json
     *
     * @param  string  $blockReference  The block reference (template key)
     * @param  string|null  $themeName  Optional theme name, defaults to active theme
     * @return array|null Null if not found
     */
    public function getTemplateConfig(string $blockReference, ?string $themeName = null): ?array;

    /**
     * Load and cache manifest.json for a theme
     *
     * @param  string|null  $themeName  Optional theme name, defaults to active theme
     * @return array Empty array if manifest not found
     */
    public function loadManifest(?string $themeName = null): array;

    /**
     * Clear manifest cache for a theme
     *
     * @param  string|null  $themeName  Optional theme name, defaults to active theme
     */
    public function clearCache(?string $themeName = null): void;
}
