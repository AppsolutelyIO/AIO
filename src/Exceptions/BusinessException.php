<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Generic business logic exception
 *
 * Use specific business exceptions when possible.
 * This is a fallback for general business rule violations.
 */
class BusinessException extends BaseBusinessException
{
    public function __construct(
        string $message = 'A business rule violation occurred',
        int $code = 1000,
        array $errors = [],
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $errors, $userMessage, $previous, $context);
    }
}
