<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Exceptions\StorageException;
use Appsolutely\AIO\Helpers\FileHelper;
use Appsolutely\AIO\Jobs\OptimizeImageJob;
use Appsolutely\AIO\Models\AdminSetting;
use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Models\Model;
use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Repositories\AdminSettingRepository;
use Appsolutely\AIO\Repositories\FileRepository;
use Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface;
use Appsolutely\AIO\Services\Contracts\StorageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class StorageService implements StorageServiceInterface
{
    public function __construct(
        protected AdminSettingRepository $adminSettingRepository,
        protected FileRepository $fileRepository,
        protected ImageOptimizationServiceInterface $imageOptimizationService,
    ) {}

    private function getFilePath(File $file): string
    {
        return $file->path . '/' . $file->filename;
    }

    public function store(UploadedFile $file): File
    {
        $originalFilename = $file->getClientOriginalName();
        $extension        = $file->getClientOriginalExtension();
        $mimeType         = $file->getMimeType();
        $size             = $file->getSize();
        $hash             = hash_file('sha256', $file->getRealPath());

        // Deduplication: if a file with the same hash already exists, reuse it
        $existing = $this->findByHash($hash);
        if ($existing !== null) {
            return $existing;
        }

        // Generate path based on current year and month (YYYY/MM)
        $path = now()->format('Y/m');

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;

        // Extract image dimensions if applicable
        $width  = null;
        $height = null;
        if (in_array(strtolower($extension), FileHelper::IMAGE_EXTENSIONS, true)) {
            $imageSize = @getimagesize($file->getRealPath());
            if ($imageSize !== false) {
                $width  = $imageSize[0];
                $height = $imageSize[1];
            }
        }

        try {
            // Store file to S3
            $filePath = $path . '/' . $filename;
            $result   = Storage::disk('s3')->putFileAs(
                $path,
                $file,
                $filename,
                ['visibility' => 'public']
            );

            if (! $result) {
                throw new StorageException(
                    "Failed to upload file '{$originalFilename}' to S3 storage. Path: {$filePath}",
                    'Unable to upload the file. Please try again.',
                    null,
                    ['filename' => $originalFilename, 'path' => $filePath]
                );
            }

            // Verify file exists
            if (! Storage::disk('s3')->exists($filePath)) {
                throw new StorageException(
                    "File '{$originalFilename}' was not found in S3 storage after upload. Expected path: {$filePath}",
                    'The uploaded file could not be verified. Please try uploading again.',
                    null,
                    ['filename' => $originalFilename, 'path' => $filePath]
                );
            }

            // Create database record using FileRepository
            return $this->fileRepository->create([
                'original_filename' => $originalFilename,
                'filename'          => $filename,
                'extension'         => $extension,
                'mime_type'         => $mimeType,
                'path'              => $path,
                'size'              => $size,
                'hash'              => $hash,
                'width'             => $width,
                'height'            => $height,
                'disk'              => 's3',
            ]);
        } catch (StorageException $e) {
            // Re-throw StorageException as-is
            throw $e;
        } catch (\Exception $e) {
            log_error('S3 Upload failed', [
                'file'  => $originalFilename,
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            throw new StorageException(
                "Failed to store file '{$originalFilename}': {$e->getMessage()}",
                'Unable to store the file. Please try again.',
                $e,
                ['filename' => $originalFilename, 'path' => $path]
            );
        }
    }

    public function delete(File $file): bool
    {
        $disk = $file->disk ?? 's3';

        // Delete optimized versions from S3
        $attachments = $file->attachments()->whereNotNull('optimized_path')->get();
        foreach ($attachments as $attachment) {
            Storage::disk($disk)->delete($attachment->optimized_path);
        }

        // Delete original from S3
        if (Storage::disk($disk)->delete($this->getFilePath($file))) {
            return $this->fileRepository->delete($file->id);
        }

        return false;
    }

    public function getSignedUrl(File $file, int $expiresInMinutes = 5): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $this->getFilePath($file),
            now()->addMinutes($expiresInMinutes)
        );
    }

    /**
     * Find a file by its ID.
     */
    public function findFile(int $id): \Illuminate\Database\Eloquent\Model
    {
        return $this->fileRepository->find($id);
    }

    /**
     * Find an existing file by its SHA-256 hash.
     */
    public function findByHash(string $hash): ?File
    {
        return $this->fileRepository->findByHash($hash);
    }

    /**
     * Retrieve a file from storage, pulling from S3 if not found locally.
     * When serving assets, resolves via file attachment and serves the optimized version if available.
     *
     * @param  string  $filePath  Full file path including filename
     * @param  bool  $isAssetsRoute  Whether this is an assets route (resolve via file attachment)
     * @return array{0: string, 1: string}|null Returns [file contents, mime type] if found, null if not found
     */
    public function retrieve(string $filePath, bool $isAssetsRoute = false): ?array
    {
        if ($isAssetsRoute) {
            $attachment = $this->fileRepository->findByAttachment($filePath);
            if (empty($attachment?->file?->full_path)) {
                abort(404);
            }

            // Serve optimized version if available and browser supports it
            if ($attachment->hasOptimizedVersion() && $this->browserAcceptsFormat($attachment->optimized_format)) {
                $optimizedResult = $this->retrieveFromStorage($attachment->optimized_path);
                if ($optimizedResult !== null) {
                    return $optimizedResult;
                }
            }

            $filePath = $attachment->file->full_path;
        }

        return $this->retrieveFromStorage($filePath);
    }

    /**
     * Check if the browser accepts a given image format via Accept header.
     */
    private function browserAcceptsFormat(?string $format): bool
    {
        if ($format === null) {
            return false;
        }

        $accept = request()->header('Accept', '');

        return match ($format) {
            'webp'  => str_contains($accept, 'image/webp') || str_contains($accept, 'image/*') || str_contains($accept, '*/*'),
            'avif'  => str_contains($accept, 'image/avif') || str_contains($accept, 'image/*') || str_contains($accept, '*/*'),
            default => true,
        };
    }

    /**
     * Retrieve file contents from storage with local caching.
     *
     * @return array{0: string, 1: string}|null
     */
    private function retrieveFromStorage(string $filePath): ?array
    {
        // Check if file exists in local storage
        $localFilePath = appsolutely() . '/' . $filePath;
        if (Storage::disk('public')->exists($localFilePath)) {
            return [
                Storage::disk('public')->get($localFilePath),
                Storage::disk('public')->mimeType($localFilePath),
            ];
        }

        try {
            // If not in local storage, attempt to get from S3
            $s3Contents = Storage::disk('s3')->get($filePath);
            if (empty($s3Contents)) {
                return null;
            }

            // Store the file locally
            Storage::disk('public')->put($localFilePath, $s3Contents);

            return [
                $s3Contents,
                Storage::disk('public')->mimeType($localFilePath),
            ];
        } catch (\Exception $e) {
            log_error('Failed to retrieve file from S3', [
                'filePath' => $filePath,
                'error'    => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function attach(File $file): false|string|null
    {
        $filePath = null;
        $class    = request()->query('class');
        $key      = request()->query('id');
        $type     = request()->get('upload_column') ?? request()->query('type');

        if (in_array($type, array_keys(AdminSetting::PATH_PATTERNS)) && $pattern = config(AdminSetting::PATH_PATTERNS[$type])) {
            $adminSetting = $this->adminSettingRepository->findByFieldFirst('slug', 'site-settings')
                ?? AdminSetting::firstOrCreate(['slug' => 'site-settings'], ['value' => '{}']);
            $filePath     = sprintf($pattern, $file->extension);
            $sync         = [$file->id => ['type' => $type, 'file_path' => $filePath]];
            $adminSetting->filesOfType($type)->sync($sync);

            $this->dispatchOptimizationIfImage($file, $filePath);
        } elseif (! empty($class) && ! empty($key) && class_exists($class) && is_a($class, Model::class, true)) {
            $object = (new $class())->find($key);
            if (! method_exists($object, 'filesOfType')) {
                return false;
            }
            if ($object instanceof ReleaseBuild) {
                $pattern   = 'release/v%s/%s';
                $build     = (new $class())::with(['version'])->find($key);
                $subFolder = $build?->version->version;
                $filePath  = sprintf($pattern, $subFolder, $file->original_filename);
            } else {
                $pattern   = '%s/%s/%s.%s';
                $folder    = Str::plural(Str::kebab(class_basename($class)));
                $subFolder = $object->slug ?? $key;
                $filename  = $type;
                $filePath  = sprintf($pattern, $folder, $subFolder, $filename, $file->extension);
            }

            $sync = [$file->id => ['type' => $type, 'file_path' => $filePath]];
            $object->filesOfType($type)->sync($sync);

            $this->dispatchOptimizationIfImage($file, $filePath);
        }

        return $filePath;
    }

    /**
     * Dispatch image optimization job if the file is an optimizable image.
     */
    private function dispatchOptimizationIfImage(File $file, string $filePath): void
    {
        if (! $this->imageOptimizationService->isOptimizable($file)) {
            return;
        }

        $attachment = FileAttachment::query()
            ->where('file_id', $file->id)
            ->where('file_path', $filePath)
            ->first();

        if ($attachment !== null) {
            OptimizeImageJob::dispatch($attachment);
        }
    }

    public function getLibrary(Request $request): LengthAwarePaginator
    {
        return $this->fileRepository->getLibrary($request);
    }

    public function response(Request $request, ?string $filePath = null, bool $isAssetsRoute = false): Response|JsonResponse
    {
        $result = $this->retrieve($filePath, $isAssetsRoute);
        if ($result === null) {
            abort(404);
        }

        [$fileContents, $mimeType] = $result;
        $fileName                  = basename($filePath);
        $etag                      = md5($fileContents);

        // Return 304 Not Modified if ETag matches
        if ($request->header('If-None-Match') === '"' . $etag . '"') {
            return response('', 304)
                ->header('ETag', '"' . $etag . '"')
                ->header('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Force download if 'download' parameter is present in the query
        if ($request->has('download')) {
            $disposition = 'attachment';
        } else {
            $disposition = in_array($mimeType, FileHelper::DISPLAYABLE_MIME_TYPES) ? 'inline' : 'attachment';
        }

        $response = response($fileContents)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', $disposition . '; filename="' . $fileName . '"')
            ->header('Content-Length', (string) strlen($fileContents))
            ->header('ETag', '"' . $etag . '"')
            ->header('Cache-Control', 'public, max-age=31536000, immutable');

        // Add Vary header for content negotiation (WebP/AVIF support)
        if (str_starts_with($mimeType, 'image/')) {
            $response->header('Vary', 'Accept');
        }

        return $response;
    }
}
