<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for database transaction failures
 */
final class TransactionException extends BaseSystemException
{
    public function __construct(
        string $message = 'Database transaction failed',
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct(
            $message,
            'Database',
            500,
            $userMessage ?? 'A database error occurred. Please try again. If the problem persists, contact support.',
            $previous,
            $context
        );
    }
}
