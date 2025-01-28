<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function respondSuccess($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function respondError($message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    protected function respondCreated($data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->respondSuccess($data, $message, 201);
    }

    protected function respondNoContent(string $message = 'No content'): JsonResponse
    {
        return $this->respondSuccess(null, $message, 204);
    }

    protected function respondNotFound(string $message = 'Not found'): JsonResponse
    {
        return $this->respondError($message, 404);
    }

    protected function respondUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->respondError($message, 401);
    }

    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->respondError($message, 403);
    }

    protected function respondValidationErrors($errors): JsonResponse
    {
        return $this->respondError('Validation failed', 422, $errors);
    }
} 