<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface StorageServiceInterface
{
    /**
     * Store an uploaded file, with optional hash-based deduplication.
     */
    public function store(UploadedFile $file): File;

    /**
     * Delete a file and its optimized versions.
     */
    public function delete(File $file): bool;

    /**
     * Get a signed URL for a file.
     */
    public function getSignedUrl(File $file, int $expiresInMinutes = 5): string;

    /**
     * Find a file by its ID.
     */
    public function findFile(int $id): \Illuminate\Database\Eloquent\Model;

    /**
     * Find an existing file by its SHA-256 hash.
     */
    public function findByHash(string $hash): ?File;

    /**
     * Retrieve a file from storage, pulling from S3 if not found locally.
     *
     * @param  string  $filePath  Full file path including filename
     * @param  bool  $isAssetsRoute  Whether this is an assets route (resolve via file attachment)
     * @return array{0: string, 1: string}|null Returns [file contents, mime type] if found, null if not found
     */
    public function retrieve(string $filePath, bool $isAssetsRoute = false): ?array;

    /**
     * Attach a file to its associated model via file attachment.
     */
    public function attach(File $file): false|string|null;

    /**
     * Get file library with pagination.
     */
    public function getLibrary(Request $request): LengthAwarePaginator;

    /**
     * Get file response with proper cache headers.
     */
    public function response(Request $request, ?string $filePath = null, bool $isAssetsRoute = false): Response|JsonResponse;
}
