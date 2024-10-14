<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Product API",
 *     version="1.0.0",
 *     description="API of sowgatly.app"
 * )
 * @OA\Server(
 *     description="Local server",
 *     url=L5_SWAGGER_CONST_LOCAL_HOST
 * )
 * @OA\Server(
 *     description="Remote server",
 *     url=L5_SWAGGER_CONST_REMOTE_HOST
 * )
 */
class SwaggerController extends Controller
{
    // This controller doesn't need any methods
}