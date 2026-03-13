<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

use Exception;

/**
 * Base exception class for all application exceptions
 *
 * Provides common functionality and structure for all custom exceptions.
 * All application-specific exceptions should extend this class.
 */
abstract class BaseException extends Exception
{
    /**
     * User-friendly error message (shown to end users)
     */
    protected string $userMessage;

    /**
     * Technical error message (for logging/debugging)
     */
    protected string $technicalMessage;

    /**
     * Additional context data for error reporting
     */
    protected array $context = [];

    public function __construct(
        string $message = 'An error occurred',
        int $code = 500,
        ?\Throwable $previous = null,
        ?string $userMessage = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->technicalMessage  = $message;
        $this->userMessage       = $userMessage ?? $this->getDefaultUserMessage();
        $this->context           = $context;
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get technical error message
     */
    public function getTechnicalMessage(): string
    {
        return $this->technicalMessage;
    }

    /**
     * Get context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get default user-friendly message
     * Override in child classes for specific messages
     */
    protected function getDefaultUserMessage(): string
    {
        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Convert exception to array for API responses
     */
    public function toArray(): array
    {
        return [
            'message'   => $this->getUserMessage(),
            'code'      => $this->getCode(),
            'context'   => $this->context,
            'technical' => config('app.debug') ? $this->getTechnicalMessage() : null,
        ];
    }
}
