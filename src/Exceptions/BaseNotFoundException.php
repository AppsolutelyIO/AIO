<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

/**
 * Base exception for resource not found errors
 *
 * All "not found" exceptions should extend this class.
 * Provides consistent user-friendly messaging for missing resources.
 */
abstract class BaseNotFoundException extends BaseException
{
    /**
     * Resource type (e.g., "Page", "Form", "User")
     */
    protected string $resourceType;

    /**
     * Resource identifier that was searched
     */
    protected string $identifier;

    public function __construct(
        string $identifier,
        string $resourceType = 'Resource',
        ?string $userMessage = null,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        $this->identifier   = $identifier;
        $this->resourceType = $resourceType;

        $technicalMessage  = "{$resourceType} not found: {$identifier}";
        $userMessage       = $userMessage ?? $this->getDefaultUserMessage();

        parent::__construct(
            $technicalMessage,
            404,
            $previous,
            $userMessage,
            array_merge($context, [
                'resource_type' => $resourceType,
                'identifier'    => $identifier,
            ])
        );
    }

    /**
     * Get the resource identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the resource type
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * Get default user-friendly message
     */
    protected function getDefaultUserMessage(): string
    {
        return "The requested {$this->resourceType} could not be found.";
    }
}
