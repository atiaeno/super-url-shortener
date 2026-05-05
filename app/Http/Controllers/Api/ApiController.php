<?php
// © Atia Hegazy — atiaeno.com

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    /**
     * Return a standardized success response.
     */
    protected function success(mixed $data, string $message = 'Success', int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a standardized error response.
     */
    protected function error(string $message, int $code = 400, ?array $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a standardized paginated response.
     */
    protected function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'total_count' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
