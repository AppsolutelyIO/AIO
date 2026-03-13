<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Config;

/**
 * Type-safe configuration accessor for mail application settings
 *
 * This class provides typed access to all mail configuration values
 * stored in the admin settings system. All methods return properly typed
 * values with null safety where appropriate.
 *
 * Usage:
 *   $config = new MailConfig();
 *   $value = $config->methodName(); // Returns string|null
 *
 * Or use the static helper:
 *   MailConfig::getMethodName();
 */
final readonly class MailConfig
{
    /**
     * Get the Server
     */
    public function server(): ?string
    {
        return config('mail.server');
    }

    /**
     * Get the port
     */
    public function port(): ?string
    {
        return config('mail.port');
    }

    /**
     * Get the Username
     */
    public function username(): ?string
    {
        return config('mail.username');
    }

    /**
     * Get the Password
     */
    public function password(): ?string
    {
        return config('mail.password');
    }

    // Static helper methods for convenience

    /**
     * Get the Server (static)
     */
    public static function getServer(): ?string
    {
        return (new self())->server();
    }

    /**
     * Get the port (static)
     */
    public static function getPort(): ?string
    {
        return (new self())->port();
    }

    /**
     * Get the Username (static)
     */
    public static function getUsername(): ?string
    {
        return (new self())->username();
    }

    /**
     * Get the Password (static)
     */
    public static function getPassword(): ?string
    {
        return (new self())->password();
    }
}
