<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;

interface ImageOptimizationServiceInterface
{
    /**
     * Optimize an image file and return metadata about the optimized version.
     *
     * @param  array{format?: string, quality?: int, maxWidth?: int, maxHeight?: int}  $options
     * @return array{path: string, format: string, size: int, width: int, height: int}
     */
    public function optimize(File $file, array $options = []): array;

    /**
     * Generate an optimized version for a file attachment record.
     */
    public function optimizeForAttachment(FileAttachment $attachment): FileAttachment;

    /**
     * Check if a file is an optimizable image.
     */
    public function isOptimizable(File $file): bool;

    /**
     * Extract image dimensions from a file path on a given disk.
     *
     * @return array{width: int, height: int}|null
     */
    public function extractDimensions(string $filePath, string $disk = 's3'): ?array;
}
