<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\Shop;
use App\Rules\TurkmenistanPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
     *             @OA\Property(property="recipient_name", type="string", nullable=true, example="Aýna"),
     *             @OA\Property(property="delivery_address", type="string", nullable=true, description="Required unless fulfillment is pickup", example="Aýtakow köç. 17"),
     *             @OA\Property(property="fulfillment", type="string", enum={"delivery", "pickup"}, default="delivery"),
     *             @OA\Property(property="payment_method", type="string", enum={"cash", "online"}, default="cash"),
     *             @OA\Property(property="payment_bank", type="string", nullable=true, description="Required when payment_method is online; a code from GET /api/payment-methods", example="rysgal"),
     *             @OA\Property(property="note", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="One order per shop in the cart. `order` is the first one (kept for older clients), `orders` lists all of them.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="order", type="object"),
     *             @OA\Property(property="orders", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="422", description="Validation error"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function createOrder(Request $request)
    {
        // Old clients never send fulfillment / payment_method: defaults keep
        // their payload valid and produce the same kind of order as before.
        $fulfillment = $request->input('fulfillment', 'delivery');
        $onlineBanks = collect(config('payments.methods'))->where('type', 'online')->pluck('code')->all();

        $validator = Validator::make($request->all(), [
            'delivery_type' => 'required|in:asap,scheduled',
            'scheduled_at' => 'required_if:delivery_type,scheduled|nullable|date|after:now',
            'recipient_phone' => ['required', new TurkmenistanPhoneNumber],
            'recipient_name' => 'nullable|string|max:255',
            'fulfillment' => 'nullable|in:delivery,pickup',
            'delivery_address' => [
                $fulfillment === 'pickup' ? 'nullable' : 'required',
                'string',
                'max:500',
            ],
            'payment_method' => 'nullable|in:cash,online',
            'payment_bank' => [
                'required_if:payment_method,online',
                'nullable',
                'string',
                Rule::in($onlineBanks),
            ],
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

        // One order per shop: each shop fulfils, prices and charges delivery
        // for its own items. Shop ids come from the products, never the client.
        $groups = $cart->items->groupBy(fn ($item) => $item->product->shop_id);
        $shops = Shop::whereIn('id', $groups->keys())->get()->keyBy('id');

        if ($fulfillment === 'pickup') {
            foreach ($groups->keys() as $shopId) {
                $shop = $shops->get($shopId);
                if ($shop && !$shop->pickup_available) {
                    return response()->json(['success' => false, 'message' => 'This shop does not offer pickup'], 422);
                }
            }
        }

        $paymentMethod = $request->input('payment_method', 'cash');

        DB::beginTransaction();

        try {
            $orders = collect();

            foreach ($groups as $shopId => $items) {
                $shop = $shops->get($shopId);

                $itemsTotal = $items->sum(function ($item) {
                    return $item->quantity * $item->product->getDiscountedPrice();
                });

                // The shop's fee applies to delivery only; pickup is free.
                $deliveryFee = $fulfillment === 'delivery' ? (float) ($shop->delivery_fee ?? 0) : 0.0;

                $order = Order::create([
                    'user_id' => $user->id,
                    'shop_id' => $shopId,
                    'items_total' => $itemsTotal,
                    'delivery_fee' => $deliveryFee,
                    'total_amount' => $itemsTotal + $deliveryFee,
                    'status' => 'pending',
                    'fulfillment' => $fulfillment,
                    'delivery_type' => $request->delivery_type,
                    'scheduled_at' => $request->delivery_type === 'scheduled' ? $request->scheduled_at : null,
                    'recipient_phone' => $request->recipient_phone,
                    'recipient_name' => $request->recipient_name,
                    'delivery_address' => $fulfillment === 'pickup' ? null : $request->delivery_address,
                    'note' => $request->note,
                    'payment_method' => $paymentMethod,
                    // Online payment is not connected to a bank yet: the chosen
                    // bank is recorded and the order stays unpaid until settled.
                    'payment_bank' => $paymentMethod === 'online' ? $request->payment_bank : null,
                    'payment_status' => 'unpaid',
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->getDiscountedPrice()
                    ]);

                    $item->product->decrement('stock', $item->quantity);
                }

                $orders->push($order->load('items.product'));
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                // `order` stays the first order so older clients keep working;
                // `orders` lists every per-shop order created by this checkout.
                'order' => $orders->first(),
                'orders' => $orders->values(),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Order creation failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/cancel",
     *     summary="Cancel a pending order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Order cancelled"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="422", description="Order is no longer pending"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function cancel($id)
    {
        $order = Order::where('user_id', Auth::id())->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Customers can only cancel before the shop starts preparing it.
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->restock();
            $order->update(['status' => 'cancelled']);
        });

        return response()->json(['success' => true, 'order' => $order->fresh()->load('items.product.images', 'shop')]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get user's orders",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="q", in="query", required=false, description="Search by order number or product name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending","processing","delivering","completed","cancelled"})),
     *     @OA\Response(response="200", description="Successful operation"),
     *     security={{"bearerAuth": {}}}
     * )
     * @OA\Get(
     *     path="/api/user/orders",
     *     summary="Legacy alias of GET /api/orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Successful operation")
     * )
     */
    public function getUserOrders(Request $request)
    {
        $user = Auth::user();
        $orders = Order::with('items.product.images', 'shop')
            ->where('user_id', $user->id)
            ->search($request->query('q'))
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->query('status'));
            })
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
        $order = Order::with('items.product.images', 'shop')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // `reviewed` (additive) tells the app which items still need a review.
        $reviewed = ProductReview::where('user_id', $user->id)
            ->whereIn('product_id', $order->items->pluck('product_id'))
            ->pluck('product_id')
            ->all();

        $order->items->each(function ($item) use ($reviewed) {
            $item->setAttribute('reviewed', in_array($item->product_id, $reviewed, true));
        });

        return response()->json($order);
    }
}