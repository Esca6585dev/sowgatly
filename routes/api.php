<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public, read-only reference data.
Route::get('payment-methods', [App\Http\Controllers\Api\PaymentMethodController::class, 'index']);

// Anyone may apply to open a shop; the admin panel handles the applications.
Route::post('shop-applications', [App\Http\Controllers\Api\ShopApplicationController::class, 'store'])
    ->middleware('throttle:5,60');

// Login endpoints are rate limited: 4-digit codes are brute-forceable otherwise.
Route::controller(App\Http\Controllers\Api\AuthOtpController::class)->middleware('throttle:10,1')->group(function(){
    // OTP Generate route
    Route::post('otp/generate', 'generate');

    // Login with otp route
    Route::post('login', 'loginWithOtp');

    // Register with otp route
    Route::post('register', 'registerWithOtp');
});

/*
 * Read-only catalog. With APP_API_GUEST_BROWSING=true (default) these routes
 * are public: a bearer token is still honoured when present (auth.optional),
 * so authenticated responses are identical to before. With the flag off they
 * stay inside the authenticated group below, exactly as they used to be.
 */
$catalogReadRoutes = function () {
    Route::get('home', [App\Http\Controllers\Api\HomeController::class, 'index']);
    Route::get('banners', [App\Http\Controllers\Api\BannerController::class, 'index']);

    Route::get('products/{product}', [App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::get('product/search', [App\Http\Controllers\Api\ProductController::class, 'search']);
    Route::get('product/category/{category_id}', [App\Http\Controllers\Api\ProductController::class, 'getByCategory']);
    Route::get('products/{id}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'index']);

    Route::apiResource('compositions', App\Http\Controllers\Api\CompositionController::class)->only(['index', 'show']);

    Route::apiResource('categories', App\Http\Controllers\Api\CategoryController::class)->only(['index', 'show']);
    Route::get('/categories/{id}/subcategories', [App\Http\Controllers\Api\CategoryController::class, 'getSubcategories']);

    Route::get('shops/{shop}', [App\Http\Controllers\Api\ShopController::class, 'show']);

    Route::apiResource('brands', App\Http\Controllers\Api\BrandController::class)->only(['index', 'show']);

    Route::apiResource('regions', App\Http\Controllers\Api\RegionController::class)->only(['index', 'show']);
    Route::get('/regions/parent/{parent_id}', [App\Http\Controllers\Api\RegionController::class, 'getByParentId']);
};

$guestBrowsing = config('app.api_guest_browsing');

if ($guestBrowsing) {
    Route::middleware(['throttle:120,1', 'auth.optional'])->group($catalogReadRoutes);
}

Route::middleware(['auth:sanctum', 'check.token'])->group(function () use ($catalogReadRoutes, $guestBrowsing) {
    // logout route
    Route::post('logout', [App\Http\Controllers\Api\AuthOtpController::class, 'logout']);

    if (!$guestBrowsing) {
        $catalogReadRoutes();
    }

    // Catalog writes are limited to the owning shop inside the controllers;
    // categories, compositions, brands, regions and shop addresses are
    // managed from the admin panel only, so the API exposes them read-only.
    // GET products lists the caller's own shop products, so it stays here.
    Route::apiResource('products', App\Http\Controllers\Api\ProductController::class)->except(['show']);
    Route::post('products/{id}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'store']);

    // Users: only the caller's own profile. Listing/editing other users
    // (phone numbers, passwords) is admin-panel work, not API work.
    Route::get('users/me', [App\Http\Controllers\Api\UserController::class, 'me']);
    Route::put('users/me', [App\Http\Controllers\Api\UserController::class, 'updateMe']);
    // Multipart uploads cannot be sent with PUT from every client: POST with
    // the same handler (optionally with _method=PUT) does the same thing.
    Route::post('users/me', [App\Http\Controllers\Api\UserController::class, 'updateMe']);
    Route::delete('users/me/image', [App\Http\Controllers\Api\UserController::class, 'destroyImage']);
    Route::get('me/shop-applications', [App\Http\Controllers\Api\ShopApplicationController::class, 'mine']);

    // Waiting list ("Лист ожидания"): notify me when a product is back.
    Route::get('me/waitlist', [App\Http\Controllers\Api\WaitlistController::class, 'index']);
    Route::post('me/waitlist', [App\Http\Controllers\Api\WaitlistController::class, 'store']);
    Route::delete('me/waitlist/{product_id}', [App\Http\Controllers\Api\WaitlistController::class, 'destroy']);

    // Customer <-> shop chats. unread-count is declared before {id} so the
    // literal segment is not captured as an id.
    Route::get('me/chats/unread-count', [App\Http\Controllers\Api\ChatController::class, 'unreadCount']);
    Route::get('me/chats', [App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('me/chats', [App\Http\Controllers\Api\ChatController::class, 'store']);
    Route::get('me/chats/{id}/messages', [App\Http\Controllers\Api\ChatController::class, 'messages']);
    Route::post('me/chats/{id}/messages', [App\Http\Controllers\Api\ChatController::class, 'send'])->middleware('throttle:30,1');
    Route::post('me/chats/{id}/read', [App\Http\Controllers\Api\ChatController::class, 'read']);

    Route::get('shop/chats/unread-count', [App\Http\Controllers\Api\ShopChatController::class, 'unreadCount']);
    Route::get('shop/chats', [App\Http\Controllers\Api\ShopChatController::class, 'index']);
    Route::get('shop/chats/{id}/messages', [App\Http\Controllers\Api\ShopChatController::class, 'messages']);
    Route::post('shop/chats/{id}/messages', [App\Http\Controllers\Api\ShopChatController::class, 'send'])->middleware('throttle:30,1');
    Route::post('shop/chats/{id}/read', [App\Http\Controllers\Api\ShopChatController::class, 'read']);

    // Shops routes
    Route::apiResource('shops', App\Http\Controllers\Api\ShopController::class)->except(['show']);

    // Carts routes
    Route::post('cart/add', [App\Http\Controllers\Api\CartController::class, 'addToCart']);
    Route::get('cart', [App\Http\Controllers\Api\CartController::class, 'getCart']);
    Route::put('cart/items/{id}', [App\Http\Controllers\Api\CartController::class, 'updateItem']);
    Route::delete('cart/items/{id}', [App\Http\Controllers\Api\CartController::class, 'removeItem']);

    // Customer delivery addresses
    Route::get('me/addresses', [App\Http\Controllers\Api\UserAddressController::class, 'index']);
    Route::post('me/addresses', [App\Http\Controllers\Api\UserAddressController::class, 'store']);
    Route::put('me/addresses/{id}', [App\Http\Controllers\Api\UserAddressController::class, 'update']);
    Route::delete('me/addresses/{id}', [App\Http\Controllers\Api\UserAddressController::class, 'destroy']);

    // Favorites routes
    Route::get('favorites', [App\Http\Controllers\Api\FavoriteController::class, 'index']);
    Route::post('favorites/toggle', [App\Http\Controllers\Api\FavoriteController::class, 'toggle']);

    // Named favorite collections ("Подборки")
    Route::get('me/collections', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'index']);
    Route::post('me/collections', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'store']);
    Route::get('me/collections/{id}', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'show']);
    Route::put('me/collections/{id}', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'update']);
    Route::delete('me/collections/{id}', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'destroy']);
    Route::post('me/collections/{id}/products', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'addProduct']);
    Route::delete('me/collections/{id}/products/{product_id}', [App\Http\Controllers\Api\FavoriteCollectionController::class, 'removeProduct']);

    // Order routes
    // Not an apiResource: OrderController exposes createOrder/getUserOrders/getOrder
    // rather than the store/index/show names apiResource expects.
    Route::post('orders', [App\Http\Controllers\Api\OrderController::class, 'createOrder']);
    Route::get('orders', [App\Http\Controllers\Api\OrderController::class, 'getUserOrders']);
    Route::get('orders/{id}', [App\Http\Controllers\Api\OrderController::class, 'getOrder']);
    Route::post('orders/{id}/cancel', [App\Http\Controllers\Api\OrderController::class, 'cancel']);

    // Orders for the signed-in user's own shop
    Route::get('shop/orders', [App\Http\Controllers\Api\ShopOrderController::class, 'index']);
    Route::put('shop/orders/{id}/status', [App\Http\Controllers\Api\ShopOrderController::class, 'updateStatus']);

    // In-app notifications
    Route::get('me/notifications/unread-count', [App\Http\Controllers\Api\UserNotificationController::class, 'unreadCount']);
    Route::get('me/notifications', [App\Http\Controllers\Api\UserNotificationController::class, 'index']);
    Route::post('me/notifications/read', [App\Http\Controllers\Api\UserNotificationController::class, 'markAllRead']);
    Route::get('user/orders', [App\Http\Controllers\Api\OrderController::class, 'getUserOrders']);

    // Address routes
    Route::apiResource('addresses', App\Http\Controllers\Api\AddressController::class)->only(['index', 'show']);
});
