<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface TurnstileServiceInterface
{
    /**
     * Verify a Turnstile token with the Cloudflare API.
     */
    public function verify(string $token, ?string $remoteIp = null): bool;

    /**
     * Check if Turnstile verification is enabled.
     */
    public function isEnabled(): bool;
}
