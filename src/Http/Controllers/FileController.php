<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Services\Contracts\StorageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class FileController extends BaseController
{
    public function __construct(
        private readonly StorageServiceInterface $storageService
    ) {}

    /**
     * Retrieve a file from storage
     *
     * @param  string|null  $filePath  Full file path including filename
     */
    public function retrieve(Request $request, ?string $filePath = null): Response|JsonResponse
    {
        if (empty($filePath)) {
            abort(404);
        }

        $isAssetsRoute = $request->route()?->getName() === 'file.public.assets';

        return $this->storageService->response($request, $filePath, $isAssetsRoute);
    }
}
