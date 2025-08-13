<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
use App\Constants\ApiCodes;

/**
 * Base API Controller - Base controller for all API endpoints
 * Extends Laravel Controller and provides standardized response methods
 */
abstract class BaseApiController extends Controller
{
    /**
     * Smart response method - automatically handles array pattern [data, message, code]
     * 
     * @param array $responseArray Array with format [data, message, code] or [data, message, code, errors]
     * @return JsonResponse Formatted response
     */
    protected function apiResponse(array $responseArray): JsonResponse
    {
        // Extract data, message, code from array by key
        $data = $responseArray['data'] ?? null;
        $message = $responseArray['message'] ?? '';
        $code = $responseArray['code'] ?? ApiCodes::SUCCESS;
        $errors = $responseArray['errors'] ?? null;
        
        // Build response based on code
        $response = [
            'success' => $code < ApiCodes::BAD_REQUEST,  // true if success, false if error
            'message' => $message ?: ApiCodes::getMessage($code),  // Use default message for code if no message provided
            'code' => $code,
        ];

        // Add errors if present
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // Add data if present and not null
        if ($data !== null) {
            $response['data'] = $this->formatData($data);
        }

        // Return JSON response
        return response()->json($response, $code);
    }



    /**
     * Format data based on data type
     * 
     * @param mixed $data Data to format
     * @return mixed Formatted data
     */
    protected function formatData($data)
    {
        // If it's an Eloquent Model, convert to array
        if ($data instanceof Model) {
            return $data->toArray();
        }

        // If it's a Paginator, format into pagination structure
        if ($data instanceof LengthAwarePaginator) {
            return [
                'data' => $data->items(),           // List of items
                'pagination' => [
                    'current_page' => $data->currentPage(),  // Current page
                    'per_page' => $data->perPage(),          // Items per page
                    'total' => $data->total(),               // Total items
                    'last_page' => $data->lastPage(),       // Last page
                ]
            ];
        }

        // If it's a Collection, convert to array
        if ($data instanceof Collection) {
            return $data->toArray();
        }

        // If it's already an array, keep as is
        if (is_array($data)) {
            return $data;
        }

        // For other data types, return as is
        return $data;
    }
} 