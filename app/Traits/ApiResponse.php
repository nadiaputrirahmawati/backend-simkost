<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

trait ApiResponse
{
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $code = 200,
    ): JsonResponse {
        $payload = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $code);
    }

    protected function errorResponse(
        string $message,
        int $code = 400,
        ?array $errors = null,
    ): JsonResponse {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    protected function validationErrorResponse(
        \Illuminate\Validation\Validator $validator,
    ): JsonResponse {
        return $this->errorResponse(
            'Validasi gagal.',
            422,
            $validator->errors()->toArray(),
        );
    }

    protected function paginateResponse(
        ResourceCollection $resource,
        string $message = 'Data berhasil diambil.',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resource->response()->getData(true)['data'],
            'meta' => [
                'current_page' => $resource->resource->currentPage(),
                'last_page' => $resource->resource->lastPage(),
                'per_page' => $resource->resource->perPage(),
                'total' => $resource->resource->total(),
            ],
        ]);
    }
}
