<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for configuration errors
 */
final class ConfigurationException extends BaseSystemException
{
    public function __construct(
        string $message = 'Configuration error',
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct(
            $message,
            'Configuration',
            500,
            $userMessage ?? 'A configuration error occurred. Please contact support.',
            $previous,
            $context
        );
    }
}
