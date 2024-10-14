<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="API Endpoints for Cart operations"
 * )
 */
class CartController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Add product to cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response="200", description="Product added to cart"),
     *     @OA\Response(response="404", description="Product not found"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id
            ],
            ['quantity' => $request->quantity]
        );

        return response()->json([
            'message' => 'Product added to cart',
            'cart_item' => $cartItem->load('product')
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Get user's cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getCart()
    {
        $user = Auth::user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty']);
        }

        $totalAmount = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->getDiscountPrice();
        });

        return response()->json([
            'cart' => $cart,
            'total_amount' => $totalAmount
        ]);
    }
}