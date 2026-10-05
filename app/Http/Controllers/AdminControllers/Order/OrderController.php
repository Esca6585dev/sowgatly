<?php

namespace App\Http\Controllers\AdminControllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $filters = [
            'status' => Order::STATUSES,
            'payment_status' => self::PAYMENT_STATUSES,
            'payment_method' => ['cash', 'online'],
            'fulfillment' => ['delivery', 'pickup'],
        ];

        $orders = Order::with('user:id,name,phone_number', 'shop:id,name')
            ->withSum('items as items_quantity', 'quantity')
            ->search($request->input('search'))
            ->when($request->filled('shop_id'), fn ($q) => $q->where('shop_id', (int) $request->input('shop_id')))
            ->tap(function ($q) use ($request, $filters) {
                foreach ($filters as $field => $allowed) {
                    if (in_array($request->input($field), $allowed, true)) {
                        $q->where($field, $request->input($field));
                    }
                }
            })
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.order.order-table', compact('orders', 'pagination'));
        }

        // Counts follow the shop filter (the Shops page links here with ?shop_id=).
        $shop = $request->filled('shop_id') ? Shop::find((int) $request->input('shop_id'), ['id', 'name']) : null;
        $statusCounts = Order::when($request->filled('shop_id'), fn ($q) => $q->where('shop_id', (int) $request->input('shop_id')))
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin-panel.order.order', compact('orders', 'pagination', 'statusCounts', 'shop'));
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
            'payment_status' => 'nullable|in:' . implode(',', self::PAYMENT_STATUSES),
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
