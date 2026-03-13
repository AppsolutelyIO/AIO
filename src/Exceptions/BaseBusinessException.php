<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Base exception for business logic errors
 *
 * Used for errors that occur due to business rules or validation.
 * These are expected errors that should be handled gracefully.
 */
abstract class BaseBusinessException extends BaseException
{
    /**
     * Additional validation errors
     */
    protected array $errors = [];

    public function __construct(
        string $message = 'A business rule violation occurred',
        int $code = 1000,
        array $errors = [],
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $this->errors = $errors;

        parent::__construct(
            $message,
            $code,
            $previous,
            $userMessage ?? $this->getDefaultUserMessage(),
            array_merge($context, ['errors' => $errors])
        );
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if exception has errors
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Get default user-friendly message
     */
    protected function getDefaultUserMessage(): string
    {
        return 'The operation could not be completed. Please check your input and try again.';
    }

    /**
     * Convert exception to array for API responses
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if ($this->hasErrors()) {
            $data['errors'] = $this->errors;
        }

        return $data;
    }
}
