<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface ThemeServiceInterface
{
    /**
     * Resolve the active theme name based on configuration
     */
    public function resolveThemeName(): ?string;

    /**
     * Get the parent theme name from configuration
     */
    public function getParentTheme(): ?string;

    /**
     * Check if theme should be applied for the given request
     */
    public function shouldApplyTheme(string $path): bool;

    /**
     * Set up the theme view finder and paths
     */
    public function setupTheme(string $themeName, ?string $parentTheme = null): void;

    /**
     * Get the theme path for views
     */
    public function getThemeViewPath(string $themeName): string;
}
