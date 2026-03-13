<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for page block rendering errors
 */
final class PageBlockRenderException extends BaseBusinessException
{
    public function __construct(
        string $message,
        array $errors = [],
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct(
            "Page block render error: {$message}",
            1002,
            $errors,
            $userMessage ?? 'There was an error rendering the page block. Please try refreshing the page.',
            $previous,
            array_merge($context, ['render_error' => $message])
        );
    }
}
