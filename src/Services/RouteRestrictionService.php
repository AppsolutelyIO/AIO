<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Services\Contracts\RouteRestrictionServiceInterface;

final readonly class RouteRestrictionService implements RouteRestrictionServiceInterface
{
    public function getDisabledPrefixes(): array
    {
        $disabled = config('appsolutely.features.disabled');

        if (is_string($disabled)) {
            $disabled = array_filter(array_map('trim', explode(',', $disabled)));
        }

        if (! is_array($disabled)) {
            $disabled = [];
        }

        return array_values($disabled);
    }

    public function isPrefixDisabled(string $prefix): bool
    {
        $disabledPrefixes = $this->getDisabledPrefixes();

        return in_array($prefix, $disabledPrefixes, true);
    }
}
