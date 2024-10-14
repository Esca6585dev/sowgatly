<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for Order operations"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order from cart",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="shop_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Order created"),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id'
        ]);

        $user = Auth::user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $totalAmount = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->getDiscountPrice();
        });

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'shop_id' => $request->shop_id,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->getDiscountPrice()
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order creation failed'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user's orders",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getUserOrders()
    {
        $user = Auth::user();
        $orders = Order::with('items.product', 'shop')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get a specific order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Order not found"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getOrder($id)
    {
        $user = Auth::user();
        $order = Order::with('items.product', 'shop')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json($order);
    }
}