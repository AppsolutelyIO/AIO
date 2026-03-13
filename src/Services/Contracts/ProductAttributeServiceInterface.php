<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface ProductAttributeServiceInterface
{
    /**
     * Generate cache key for attribute combination
     */
    public static function attributeCacheKey(string $groupId, string $key): string;

    /**
     * Find attributes by group ID and generate combinations
     */
    public function findAttributesByGroupId(int|string $groupId): array;
}
