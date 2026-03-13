<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Exception for invalid sitemap type errors
 */
final class InvalidSitemapTypeException extends BaseBusinessException
{
    public function __construct(
        string $type,
        array $allowedTypes = [],
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $allowedTypesStr  = ! empty($allowedTypes) ? implode(', ', $allowedTypes) : 'page, article, product';
        $message          = "Invalid sitemap type: {$type}. Allowed types: {$allowedTypesStr}";

        parent::__construct(
            $message,
            1003,
            ['type' => $type, 'allowed_types' => $allowedTypes],
            $userMessage ?? "Invalid sitemap type. Allowed types are: {$allowedTypesStr}.",
            $previous,
            array_merge($context, ['invalid_type' => $type, 'allowed_types' => $allowedTypes])
        );
    }
}
