<?php

namespace App\Http\Controllers\AdminControllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;

        $orders = Order::with('user:id,name,phone_number', 'shop:id,name', 'items')
            ->search($request->input('search'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->input('payment_status')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->input('payment_method')))
            ->when($request->filled('fulfillment'), fn ($q) => $q->where('fulfillment', $request->input('fulfillment')))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.order.order-table', compact('orders', 'pagination'))->render();
        }

        return view('admin-panel.order.order', compact('orders', 'pagination'));
    }

    public function show($lang, Order $order)
    {
        $order->load('user', 'shop', 'items.product.images', 'chatThread');
        $banks = collect(config('payments.methods'))->keyBy('code');

        return view('admin-panel.order.order-show', compact('order', 'banks'));
    }

    /**
     * Change the order status or mark it paid. Uses the same transition rules
     * as the shop-side API so the admin cannot put an order in an impossible state.
     */
    public function update(Request $request, $lang, Order $order)
    {
        $data = $request->validate([
            'status' => 'nullable|in:' . implode(',', Order::STATUSES),
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
        ]);

        if (!empty($data['status']) && $data['status'] !== $order->status) {
            if (!$order->canTransitionTo($data['status'])) {
                return back()->with('error', __('Cannot change status from :from to :to', ['from' => $order->status, 'to' => $data['status']]));
            }

            DB::transaction(function () use ($order, $data) {
                if ($data['status'] === 'cancelled') {
                    $order->restock();
                }
                $order->update(['status' => $data['status']]);
            });
        }

        if (!empty($data['payment_status']) && $data['payment_status'] !== $order->payment_status) {
            $order->update([
                'payment_status' => $data['payment_status'],
                'paid_at' => $data['payment_status'] === 'paid' ? now() : $order->paid_at,
            ]);
        }

        return redirect()->route('order.show', [app()->getLocale(), $order->id])->with('success-update', 'The resource was updated!');
    }
}
