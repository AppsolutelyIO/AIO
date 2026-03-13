<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception thrown when a form cannot be found
 */
final class FormNotFoundException extends BaseNotFoundException
{
    public function __construct(string $identifier, ?string $userMessage = null, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct(
            $identifier,
            'Form',
            $userMessage ?? 'The form you are looking for could not be found.',
            $previous,
            $context
        );
    }
}
