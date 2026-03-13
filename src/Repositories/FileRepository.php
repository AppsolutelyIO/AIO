<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use App\Helpers\FileHelper;
use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final class FileRepository extends BaseRepository
{
    public function model(): string
    {
        return File::class;
    }

    /**
     * Find a file by its filename.
     */
    public function findByFilename(string $filename): ?File
    {
        return $this->model->newQuery()->where('filename', $filename)->first();
    }

    /**
     * Find a file by its SHA-256 hash for deduplication.
     */
    public function findByHash(string $hash): ?File
    {
        return $this->model->newQuery()->where('hash', $hash)->first();
    }

    public function findByAttachment(string $filePath): ?FileAttachment
    {
        return FileAttachment::query()->with(['file'])->whereFilePath($filePath)->first();
    }

    public function getLibrary(Request $request): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Filter by file extensions (images only)
        $query->whereIn('extension', FileHelper::IMAGE_EXTENSIONS);

        // Filter by name if provided
        if ($request->has('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('original_filename', 'like', "%{$request->name}%")
                    ->orWhere('filename', 'like', "%{$request->name}%");
            });
        }

        // Filter by path if provided
        if ($request->has('path')) {
            $query->where('path', 'like', "%{$request->path}%");
        }

        // Filter by extension if provided
        if ($request->has('extension')) {
            $query->whereIn('extension', explode(',', $request->extension));
        }

        // Apply sorting
        $sortField     = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['created_at', 'original_filename', 'size', 'path'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Apply pagination
        $page     = max(1, intval($request->get('page', 1)));
        $pageSize = max(1, min(100, intval($request->get('page_size', 20)))); // Between 1 and 100

        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        // Transform the items to include full_path with app URL
        $paginator->through(function ($file) {
            $file->full_path = $file->getAttribute('full_path');
            $file->url       = upload_url($file->full_path);

            return $file;
        });

        return $paginator;
    }
}
