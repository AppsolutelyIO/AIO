<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers\Api;

use Appsolutely\AIO\Services\StorageService;
use Appsolutely\AIO\Traits\HasUploadedFile;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;

final class FileController extends AdminBaseApiController
{
    use HasUploadedFile;

    public function __construct(protected StorageService $storageService) {}

    /**
     * Handle file upload.
     */
    public function upload(Request $request): Response|JsonResponse|Redirector|RedirectResponse|Application|ResponseFactory|\Appsolutely\AIO\Http\JsonResponse
    {
        if (request()->method() == 'GET') {
            return admin_redirect('file-manager');
        }
        if ($this->isDeleteRequest()) {
            return $this->deleteFileAndResponse();
        }
        try {
            $uploadedFile = $this->file();
            if (! $uploadedFile) {
                $uploadedFile       = $request->file('file');
                if (! $uploadedFile) {
                    return admin_redirect('file-manager');
                }
            }

            $file = $this->storageService->store($uploadedFile);
            $path = $this->storageService->attach($file);

            return $this->success([
                'id'   => $path,
                'name' => $file->filename,
                'path' => $file->path,
                'url'  => Storage::disk('s3')->url($file->full_path),
            ]);
        } catch (\Exception $e) {
            log_error('Upload failed: ' . $e->getMessage());

            return $this->error($e->getMessage());
        }
    }

    /**
     * Get library
     */
    public function library(Request $request): JsonResponse
    {
        $files = $this->storageService->getLibrary($request);

        return $this->success($files);
    }
}
