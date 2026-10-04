<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Uniform JSON envelope for the newer API controllers.
 * Older controllers keep their own shapes on purpose (clients depend on them).
 */
trait RespondsWithJson
{
    protected function ok(array $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true];
        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload + $data, $status);
    }

    protected function fail(string $message, int $status = 422, $errors = null): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
