<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface ConditionalContentRendererInterface
{
    /**
     * Render content that may contain conditional blocks.
     *
     * Supports @if / @elseif / @else / @endif with whitelisted conditions.
     * Pure HTML passes through unchanged. Unknown conditions evaluate to false.
     */
    public function render(string $content): string;
}
