<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\WaitlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="Waitlist", description="Products the customer wants to be notified about")
 */
class WaitlistController extends Controller
{
    use RespondsWithJson;

    /**
     * @OA\Get(
     *     path="/api/me/waitlist",
     *     summary="Products on the customer's waiting list",
     *     tags={"Waitlist"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index(Request $request)
    {
        $items = WaitlistItem::with('product.images', 'product.shop', 'product.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->ok([
            'data' => $items->map(function (WaitlistItem $item) {
                return [
                    'id' => $item->id,
                    'product' => $item->product ? new ProductResource($item->product) : null,
                    'notified_at' => $item->notified_at,
                    'created_at' => $item->created_at,
                ];
            })->values(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/waitlist",
     *     summary="Add a product to the waiting list",
     *     tags={"Waitlist"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(@OA\JsonContent(required={"product_id"},
     *         @OA\Property(property="product_id", type="integer", example=12),
     *         @OA\Property(property="region_id", type="integer", nullable=true, example=1)
     *     )),
     *     @OA\Response(response="201", description="Added"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'region_id' => 'nullable|integer|exists:regions,id',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $item = WaitlistItem::firstOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->input('product_id')],
            ['region_id' => $request->input('region_id')]
        );

        return $this->ok(['data' => $item->load('product.images')], null, $item->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/me/waitlist/{product_id}",
     *     summary="Remove a product from the waiting list",
     *     tags={"Waitlist"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="product_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Removed"),
     *     @OA\Response(response="404", description="Not on the list"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy(Request $request, $productId)
    {
        $deleted = WaitlistItem::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return $deleted ? $this->ok() : $this->fail('Product is not on the waiting list', 404);
    }
}
