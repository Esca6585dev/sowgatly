<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Orders placed with the signed-in user's own shop.
 */
class ShopOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/shop/orders",
     *     summary="Orders placed with the caller's own shop",
     *     tags={"Shop orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Orders, newest first"),
     *     @OA\Response(response="403", description="The caller has no shop")
     * )
     */
    public function index(Request $request)
    {
        $shop = $request->user()->shop;
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'You do not have a shop'], 403);
        }

        $query = Order::with('items.product.images', 'user:id,name,phone_number')
            ->where('shop_id', $shop->id)
            ->search($request->query('q'))
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    /**
     * @OA\Put(
     *     path="/api/shop/orders/{id}/status",
     *     summary="Move one of the shop's orders to the next status",
     *     tags={"Shop orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(required={"status"}, @OA\Property(property="status", type="string", enum={"processing","delivering","completed","cancelled"}))),
     *     @OA\Response(response="200", description="Updated order"),
     *     @OA\Response(response="404", description="Order not found"),
     *     @OA\Response(response="422", description="Transition not allowed")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $shop = $request->user()->shop;
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'You do not have a shop'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:processing,delivering,completed,cancelled',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $order = Order::where('shop_id', $shop->id)->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $status = $request->input('status');
        if (!$order->canTransitionTo($status)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status from {$order->status} to {$status}",
            ], 422);
        }

        DB::transaction(function () use ($order, $status) {
            if ($status === 'cancelled') {
                $order->restock();
            }
            $order->update(['status' => $status]);
        });

        return response()->json([
            'success' => true,
            'data' => $order->fresh()->load('items.product.images', 'user:id,name,phone_number'),
        ]);
    }
}
