<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

final readonly class ImageOptimizationService implements ImageOptimizationServiceInterface
{
    private const array OPTIMIZABLE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif'];

    private const string DEFAULT_FORMAT = 'webp';

    private const int DEFAULT_QUALITY = 80;

    private const int DEFAULT_MAX_WIDTH = 1920;

    private const int DEFAULT_MAX_HEIGHT = 1080;

    /**
     * Optimize an image file and return metadata about the optimized version.
     *
     * @param  array{format?: string, quality?: int, maxWidth?: int, maxHeight?: int}  $options
     * @return array{path: string, format: string, size: int, width: int, height: int}
     */
    public function optimize(File $file, array $options = []): array
    {
        $format    = $options['format'] ?? self::DEFAULT_FORMAT;
        $quality   = $options['quality'] ?? self::DEFAULT_QUALITY;
        $maxWidth  = $options['maxWidth'] ?? self::DEFAULT_MAX_WIDTH;
        $maxHeight = $options['maxHeight'] ?? self::DEFAULT_MAX_HEIGHT;

        // Get original file contents from storage
        $disk     = $file->disk ?? 's3';
        $contents = Storage::disk($disk)->get($file->full_path);

        if ($contents === null) {
            throw new \RuntimeException("Cannot read file: {$file->full_path}");
        }

        $manager = new ImageManager(new GdDriver());
        $image   = $manager->read($contents);

        // Scale down if exceeds max dimensions (maintain aspect ratio)
        $image->scaleDown(width: $maxWidth, height: $maxHeight);

        // Encode to optimized format
        $encoded = match ($format) {
            'webp' => $image->toWebp($quality),
            'avif' => $image->toAvif($quality),
            'jpg', 'jpeg' => $image->toJpeg($quality),
            'png'   => $image->toPng(),
            default => $image->toWebp($quality),
        };

        $encodedContents = $encoded->toString();

        // Generate optimized file path
        $optimizedPath = sprintf(
            'optimized/%s/%s.%s',
            $file->path,
            pathinfo($file->filename, PATHINFO_FILENAME),
            $format
        );

        // Store optimized version to S3
        Storage::disk($disk)->put($optimizedPath, $encodedContents, ['visibility' => 'public']);

        // Get dimensions of optimized image
        $optimizedImage = $manager->read($encodedContents);

        return [
            'path'   => $optimizedPath,
            'format' => $format,
            'size'   => strlen($encodedContents),
            'width'  => $optimizedImage->width(),
            'height' => $optimizedImage->height(),
        ];
    }

    /**
     * Generate an optimized version for a file attachment record.
     */
    public function optimizeForAttachment(FileAttachment $attachment): FileAttachment
    {
        $file = $attachment->file;

        if (! $file || ! $this->isOptimizable($file)) {
            return $attachment;
        }

        $result = $this->optimize($file);

        $attachment->update([
            'optimized_path'   => $result['path'],
            'optimized_format' => $result['format'],
            'optimized_size'   => $result['size'],
            'optimized_width'  => $result['width'],
            'optimized_height' => $result['height'],
        ]);

        return $attachment->refresh();
    }

    /**
     * Check if a file is an optimizable image.
     */
    public function isOptimizable(File $file): bool
    {
        return in_array(
            strtolower($file->extension),
            self::OPTIMIZABLE_EXTENSIONS,
            true
        );
    }

    /**
     * Extract image dimensions from a file path on a given disk.
     *
     * @return array{width: int, height: int}|null
     */
    public function extractDimensions(string $filePath, string $disk = 's3'): ?array
    {
        try {
            $contents = Storage::disk($disk)->get($filePath);

            if ($contents === null) {
                return null;
            }

            $manager = new ImageManager(new GdDriver());
            $image   = $manager->read($contents);

            return [
                'width'  => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            log_error('Failed to extract image dimensions', [
                'filePath' => $filePath,
                'disk'     => $disk,
                'error'    => $e->getMessage(),
            ]);

            return null;
        }
    }
}
