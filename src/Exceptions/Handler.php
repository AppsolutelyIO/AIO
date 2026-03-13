<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function report(Throwable $e): void
    {
        // When debug is off, log full exception details to the file channel so
        // "tail storage/logs/laravel.log" shows the cause regardless of LOG_CHANNEL (e.g. stderr).
        if (! config('app.debug') && ! $this->shouldntReport($e)) {
            Log::channel('single')->error('Server error (500)', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'url'       => request()->fullUrl(),
                'method'    => request()->method(),
                'ip'        => request()->ip(),
                'user_id'   => auth()->id(),
            ]);
        }

        parent::report($e);
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            // Validation exception
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'  => false,
                    'code'    => 422,
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Authentication error
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status'  => false,
                    'code'    => 401,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Not found (custom exceptions - BaseNotFoundException and its children)
            if ($e instanceof BaseNotFoundException) {
                return response()->json([
                    'status'  => false,
                    'code'    => $e->getCode(),
                    'message' => $e->getUserMessage(),
                    'context' => config('app.debug') ? $e->getContext() : [],
                ], 404);
            }

            // Not found (HTTP)
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Route not found',
                ], 404);
            }

            // Http-specific exceptions
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'status'  => false,
                    'code'    => $e->getStatusCode(),
                    'message' => $e->getMessage() ?: 'HTTP Error',
                ], $e->getStatusCode());
            }

            if ($e instanceof BusinessException) {
                $userMessage = method_exists($e, 'getUserMessage') ? $e->getUserMessage() : $e->getMessage();

                return response()->json([
                    'status'  => false,
                    'code'    => $e->getCode(),
                    'message' => $userMessage,
                    'errors'  => $e->getErrors(),
                ], 422);
            }

            // System exceptions (BaseSystemException)
            if ($e instanceof BaseSystemException) {
                $userMessage = $e->getUserMessage();
                $httpCode    = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;

                return response()->json([
                    'status'  => false,
                    'code'    => $e->getCode(),
                    'message' => $userMessage,
                    'context' => config('app.debug') ? $e->getContext() : [],
                ], $httpCode);
            }

            // Fallback for unexpected errors
            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => config('app.debug') ? $e->getMessage() : 'Server error',
                'trace'   => config('app.debug') ? collect($e->getTrace())->take(3) : [],
            ], 500);
        }

        return parent::render($request, $e);
    }
}
