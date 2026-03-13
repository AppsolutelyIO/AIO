<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for page block related errors
 */
final class PageBlockException extends BaseBusinessException
{
    public function __construct(
        string $message = 'Page block error occurred',
        int $code = 1001,
        array $errors = [],
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct(
            $message,
            $code,
            $errors,
            $userMessage ?? 'There was an error processing the page block. Please try again.',
            $previous,
            $context
        );
    }
}
