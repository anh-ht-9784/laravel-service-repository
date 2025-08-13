<?php

namespace App\Constants;

/**
 * Basic API Response Codes Constants
 */
class ApiCodes
{
    // Success Codes (2xx)
    const SUCCESS = 200;

    // Client Error Codes (4xx)
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const UNPROCESSABLE_ENTITY = 422;

    // Server Error Codes (5xx)
    const INTERNAL_SERVER_ERROR = 500;

    // Custom Logic Codes (1000+)
    const VALIDATION_ERROR = 1000;

    /**
     * Get human readable message for a code
     */
    public static function getMessage(int $code): string
    {
        $messages = [
            self::SUCCESS => 'Success',
            self::BAD_REQUEST => 'Bad request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Resource not found',
            self::UNPROCESSABLE_ENTITY => 'Validation failed',
            self::INTERNAL_SERVER_ERROR => 'Internal server error',
            self::VALIDATION_ERROR => 'Validation error',
        ];

        return $messages[$code] ?? 'Unknown error';
    }

    /**
     * Convert code to readable format
     */
    public static function convertToReadable(int $code): string
    {
        $messages = [
            self::SUCCESS => 'SUCCESS',
            self::BAD_REQUEST => 'BAD_REQUEST',
            self::UNAUTHORIZED => 'UNAUTHORIZED',
            self::FORBIDDEN => 'FORBIDDEN',
            self::NOT_FOUND => 'NOT_FOUND',
            self::UNPROCESSABLE_ENTITY => 'VALIDATION_ERROR',
            self::INTERNAL_SERVER_ERROR => 'INTERNAL_SERVER_ERROR',
            self::VALIDATION_ERROR => 'VALIDATION_ERROR',
        ];

        return $messages[$code] ?? 'UNKNOWN_ERROR';
    }
} 