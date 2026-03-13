<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Traits;

use Appsolutely\AIO\Exceptions\BusinessException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success($data = null, string $message = 'Success', int $code = 0): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    /**
     * Success response with a list as data and top-level count (fewer nested layers).
     * Returns: { status, code, message, count, data } where data is the list.
     */
    protected function successWithCount(array $data, int $count, string $message = 'Success', int $code = 0): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'count'   => $count,
            'data'    => $data,
        ], 200);
    }

    /**
     * Success response with paginated list and meta.
     * Returns: { status, code, message, data, meta: { current_page, last_page, per_page, total, from, to } }.
     */
    protected function successWithPagination(LengthAwarePaginator $paginator, string $message = 'Success', int $code = 0): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'code'    => $code,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ], 200);
    }

    protected function error(string $message = 'Business Error', int $code = 1000, $errors = []): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => $code,
            'message' => $message,
            'errors'  => $errors,
        ], 200); // always 200 for business logic
    }

    protected function failValidation($errors): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => 422,
            'message' => 'Validation failed',
            'errors'  => $errors,
        ], 422);
    }

    protected function failAuth($message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => 401,
            'message' => $message,
        ], 401);
    }

    protected function failForbidden($message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => 403,
            'message' => $message,
        ], 403);
    }

    protected function failNotFound(string $message = 'Not found'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => 404,
            'message' => $message,
        ], 404);
    }

    protected function failServer($message = 'Server error', int $code = 500): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'code'    => $code,
            'message' => $message,
        ], 500);
    }

    protected function flattenJson($data): JsonResponse
    {
        return response()->json($data);
    }

    /**
     * @throws BusinessException
     */
    protected function throwBusinessError(string $message, int $code = 1000, array $errors = []): never
    {
        throw new BusinessException($message, $code, $errors);
    }
}
