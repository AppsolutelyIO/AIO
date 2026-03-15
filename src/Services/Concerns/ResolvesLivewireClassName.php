<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Concerns;

/**
 * Resolve legacy App\Livewire class names to AIO package namespace.
 *
 * Used by services that need to map old App\Livewire\* references
 * (stored in page_blocks.class) to the current Appsolutely\AIO\Livewire\* namespace.
 */
trait ResolvesLivewireClassName
{
    private function resolveClassName(string $className): string
    {
        if (str_starts_with($className, 'App\\Livewire\\') && ! class_exists($className)) {
            $shortName = substr($className, strlen('App\\Livewire\\'));
            $aioClass  = 'Appsolutely\\AIO\\Livewire\\' . $shortName;

            if (class_exists($aioClass)) {
                return $aioClass;
            }
        }

        return $className;
    }

    /**
     * Get all possible class name variants for database lookups.
     *
     * Returns both App\Livewire\* and Appsolutely\AIO\Livewire\* forms
     * so queries can match regardless of which namespace is stored.
     *
     * @return string[]
     */
    private function classNameVariants(string $className): array
    {
        $variants = [$className];

        if (str_starts_with($className, 'App\\Livewire\\')) {
            $variants[] = 'Appsolutely\\AIO\\Livewire\\' . substr($className, strlen('App\\Livewire\\'));
        } elseif (str_starts_with($className, 'Appsolutely\\AIO\\Livewire\\')) {
            $variants[] = 'App\\Livewire\\' . substr($className, strlen('Appsolutely\\AIO\\Livewire\\'));
        }

        return array_unique($variants);
    }
}
