<?php

namespace App\Http\Controllers\AdminControllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ChatThread;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopApplication;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function dashboard($lang)
    {
        $today = Carbon::today();
        $paid = fn ($q) => $q->where('status', '!=', 'cancelled');

        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersYesterday = Order::whereDate('created_at', $today->copy()->subDay())->count();

        $weekStart = $today->copy()->subDays(6);
        $revenueWeek = (float) Order::where($paid)->where('created_at', '>=', $weekStart)->sum('total_amount');
        $revenuePrev = (float) Order::where($paid)
            ->whereBetween('created_at', [$weekStart->copy()->subDays(7), $weekStart->copy()->subSecond()])
            ->sum('total_amount');

        // Revenue per day for the bar chart, oldest first.
        $perDay = Order::where($paid)->where('created_at', '>=', $weekStart)
            ->get(['created_at', 'total_amount'])
            ->groupBy(fn ($o) => $o->created_at->toDateString())
            ->map(fn ($g) => (float) $g->sum('total_amount'));
        $faker = config('app.faker_locales.' . app()->getLocale(), 'en_US');
        $days = collect(range(6, 0))->map(function ($ago) use ($today, $perDay, $faker) {
            $d = $today->copy()->subDays($ago);
            return ['label' => $d->copy()->locale($faker)->isoFormat('dd'), 'date' => $d->toDateString(), 'value' => $perDay[$d->toDateString()] ?? 0];
        });
        $max = max(1, $days->max('value'));

        return view('admin-panel.dashboard.dashboard', [
            'stats' => [
                'ordersToday' => $ordersToday,
                'ordersTrend' => $ordersYesterday ? (int) round(($ordersToday - $ordersYesterday) / $ordersYesterday * 100) : null,
                'revenueWeek' => $revenueWeek,
                'revenueTrend' => $revenuePrev ? round(($revenueWeek - $revenuePrev) / $revenuePrev * 100, 1) : null,
                'delivering' => Order::where('status', 'delivering')->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'applicationsNew' => ShopApplication::where('status', 'new')->count(),
                'applicationsContacted' => ShopApplication::where('status', 'contacted')->count(),
                'chatsWaiting' => ChatThread::where('shop_unread', '>', 0)->count(),
                'users' => User::count(),
                'shops' => Shop::count(),
                'products' => Product::count(),
            ],
            'days' => $days,
            'max' => $max,
            'recentOrders' => Order::with('user:id,name,phone_number,image', 'shop:id,name')->latest('id')->limit(6)->get(),
            'threads' => ChatThread::with('user:id,name,image', 'shop:id,name', 'lastMessage')
                ->orderByDesc('last_message_at')->limit(5)->get(),
        ]);
    }
}
