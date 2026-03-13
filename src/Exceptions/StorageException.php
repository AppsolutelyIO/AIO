<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for storage/file system errors
 */
final class StorageException extends BaseSystemException
{
    public function __construct(
        string $message = 'Storage operation failed',
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct(
            $message,
            'Storage',
            500,
            $userMessage ?? 'A file storage error occurred. Please try again. If the problem persists, contact support.',
            $previous,
            $context
        );
    }
}
