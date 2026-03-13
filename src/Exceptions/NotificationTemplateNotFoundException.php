<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception thrown when a notification template cannot be found
 */
final class NotificationTemplateNotFoundException extends BaseNotFoundException
{
    public function __construct(string $identifier, ?string $userMessage = null, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct(
            $identifier,
            'Notification Template',
            $userMessage ?? 'The notification template could not be found.',
            $previous,
            $context
        );
    }
}
