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

        // cart_items is keyed by cart_id (there is no user_id column) and
        // requires a price. Adding a product that is already in the cart
        // increases its quantity.
        $cartItem = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $quantity = ($cartItem->exists ? $cartItem->quantity : 0) + (int) $request->quantity;
        if ($product->stock !== null && $quantity > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock',
            ], 422);
        }

        $cartItem->quantity = $quantity;
        $cartItem->price = $product->getDiscountedPrice();
        $cartItem->save();

        return response()->json([
            'success' => true,
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
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = $this->findUserItem($id);
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $product = $cartItem->product;
        if ($product && $product->stock !== null && $request->quantity > $product->stock) {
            return response()->json(['success' => false, 'message' => 'Not enough stock'], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return $this->getCart();
    }

    public function removeItem($id)
    {
        $cartItem = $this->findUserItem($id);
        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return $this->getCart();
    }

    private function findUserItem($id)
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        return $cart ? $cart->items()->with('product')->find($id) : null;
    }

    public function getCart()
    {
        $user = Auth::user();
        $cart = Cart::with('items.product.images')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty']);
        }

        $totalAmount = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->getDiscountedPrice();
        });

        return response()->json([
            'cart' => $cart,
            'total_amount' => $totalAmount
        ]);
    }
}