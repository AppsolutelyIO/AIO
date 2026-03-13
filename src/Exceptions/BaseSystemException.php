<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Base exception for system/infrastructure errors
 *
 * Used for errors related to external systems, storage, network, etc.
 * These are typically unexpected errors that require system attention.
 */
abstract class BaseSystemException extends BaseException
{
    /**
     * System component that failed (e.g., "Database", "Storage", "Cache")
     */
    protected string $component;

    public function __construct(
        string $message = 'A system error occurred',
        string $component = 'System',
        int $code = 500,
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $this->component = $component;

        parent::__construct(
            $message,
            $code,
            $previous,
            $userMessage ?? $this->getDefaultUserMessage(),
            array_merge($context, ['component' => $component])
        );
    }

    /**
     * Get the system component
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Get default user-friendly message
     */
    protected function getDefaultUserMessage(): string
    {
        return 'A system error occurred. Our team has been notified and is working on a fix.';
    }
}
