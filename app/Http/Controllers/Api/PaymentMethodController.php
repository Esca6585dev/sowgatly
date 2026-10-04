<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(name="Payments", description="Payment methods offered at checkout")
 */
class PaymentMethodController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/payment-methods",
     *     summary="List payment methods (cash and the online banks)",
     *     tags={"Payments"},
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="code", type="string", example="rysgal"),
     *                 @OA\Property(property="type", type="string", enum={"cash","online"}),
     *                 @OA\Property(property="name", type="object",
     *                     @OA\Property(property="tm", type="string"),
     *                     @OA\Property(property="ru", type="string"),
     *                     @OA\Property(property="en", type="string")
     *                 )
     *             ))
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => array_values(config('payments.methods', [])),
        ]);
    }
}
