<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Rules\TurkmenistanPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     *     summary="Create a new order from the current cart",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"delivery_type", "recipient_phone"},
     *             @OA\Property(property="delivery_type", type="string", enum={"asap", "scheduled"}, example="asap"),
     *             @OA\Property(property="scheduled_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="recipient_phone", type="string", example="65656585"),
     *             @OA\Property(property="note", type="string", nullable=true)
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
        $validator = Validator::make($request->all(), [
            'delivery_type' => 'required|in:asap,scheduled',
            'scheduled_at' => 'required_if:delivery_type,scheduled|nullable|date|after:now',
            'recipient_phone' => ['required', new TurkmenistanPhoneNumber],
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = Auth::user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        // This app currently only supports single-shop checkout: every item
        // in the cart is expected to come from the same shop, taken from the
        // first item rather than trusted from the client.
        $shopId = $cart->items->first()->product->shop_id;

        $totalAmount = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->getDiscountedPrice();
        });

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'delivery_type' => $request->delivery_type,
                'scheduled_at' => $request->delivery_type === 'scheduled' ? $request->scheduled_at : null,
                'recipient_phone' => $request->recipient_phone,
                'note' => $request->note,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->getDiscountedPrice()
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Order creation failed'], 500);
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