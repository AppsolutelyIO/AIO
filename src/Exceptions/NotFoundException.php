<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Generic resource not found exception
 *
 * Use specific exceptions (FormNotFoundException, etc.) when possible.
 * This is a fallback for general "not found" scenarios.
 */
final class NotFoundException extends BaseNotFoundException
{
    public function __construct(string $identifier, ?string $userMessage = null, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($identifier, 'Resource', $userMessage, $previous, $context);
    }
}
