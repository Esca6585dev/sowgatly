<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Uniform JSON envelope for the endpoints added for the Flutter client.
 *
 * Existing controllers keep their own response shapes (the mobile apps depend
 * on them); only new controllers use this trait.
 */
trait RespondsWithJson
{
    /**
     * @param  array<string, mixed>  $data  Extra top-level keys merged into the envelope.
     */
    protected function ok(array $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['success' => true];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        return response()->json($payload + $data, $status);
    }

    /**
     * @param  array<string, array<int, string>>|null  $errors  Validation errors keyed by field.
     */
    protected function fail(string $message, int $status = 422, ?array $errors = null): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
