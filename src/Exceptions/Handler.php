<?php

namespace Anhht\LaravelServiceRepository\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use App\Constants\ApiCodes;

/**
 * Custom Exception Handler for API standardization
 * Catches all Laravel exceptions and formats them according to API response standard
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Only handle API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and format them according to API response standard
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Validation Exception
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'code' => ApiCodes::UNPROCESSABLE_ENTITY,
                'errors' => $e->errors()
            ], ApiCodes::UNPROCESSABLE_ENTITY);
        }

        // Authentication Exception (Sanctum, etc.)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'code' => ApiCodes::UNAUTHORIZED
            ], ApiCodes::UNAUTHORIZED);
        }

        // Authorization Exception
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'code' => ApiCodes::FORBIDDEN
            ], ApiCodes::FORBIDDEN);
        }

        // Model Not Found Exception
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'code' => ApiCodes::NOT_FOUND
            ], ApiCodes::NOT_FOUND);
        }

        // Route Not Found Exception
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
                'code' => ApiCodes::NOT_FOUND
            ], ApiCodes::NOT_FOUND);
        }

        // Method Not Allowed Exception
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'code' => ApiCodes::BAD_REQUEST
            ], ApiCodes::BAD_REQUEST);
        }

        // Query Exception (Database errors)
        if ($e instanceof QueryException) {
            $message = config('app.debug') ? $e->getMessage() : 'Database error occurred';
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => ApiCodes::INTERNAL_SERVER_ERROR
            ], ApiCodes::INTERNAL_SERVER_ERROR);
        }

        // HTTP Exception
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: 'HTTP error occurred';
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => $statusCode
            ], $statusCode);
        }

        // Generic Exception
        $message = config('app.debug') ? $e->getMessage() : 'Internal server error';
        
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => ApiCodes::INTERNAL_SERVER_ERROR
        ], ApiCodes::INTERNAL_SERVER_ERROR);
    }
} 