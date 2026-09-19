<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Favorites",
 *     description="API Endpoints for the current user's saved/favorite products"
 * )
 */
class FavoriteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     summary="List the authenticated user's favorite products",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="List of favorite products")
     * )
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'shop', 'images', 'brands'])
            ->whereIn('id', Favorite::where('user_id', $request->user()->id)->pluck('product_id'))
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * @OA\Post(
     *     path="/api/favorites/toggle",
     *     summary="Add or remove a product from the authenticated user's favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response="200", description="Favorite toggled")
     * )
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['success' => true, 'favorited' => false]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'favorited' => true]);
    }
}
