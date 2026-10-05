<?php

namespace App\Providers;

use App\Models\ChatThread;
use App\Models\Order;
use App\Models\ShopApplication;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Badges in the admin sidebar/top bar: work waiting for an admin.
        View::composer('layouts.admin-page', function ($view) {
            static $counts = null;
            if ($counts === null) {
                try {
                    $counts = [
                        'orders' => Order::where('status', 'pending')->count(),
                        'applications' => ShopApplication::where('status', 'new')->count(),
                        'chats' => ChatThread::where('shop_unread', '>', 0)->count(),
                    ];
                } catch (\Throwable $e) {
                    $counts = [];
                }
            }
            $view->with('sidebarCounts', $counts);
        });
    }
}
