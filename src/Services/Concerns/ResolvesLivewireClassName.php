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
}
