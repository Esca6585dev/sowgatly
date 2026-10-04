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

// Login endpoints are rate limited: 4-digit codes are brute-forceable otherwise.
Route::controller(App\Http\Controllers\Api\AuthOtpController::class)->middleware('throttle:10,1')->group(function(){
    // OTP Generate route
    Route::post('otp/generate', 'generate');

    // Login with otp route
    Route::post('login', 'loginWithOtp');

    // Register with otp route
    Route::post('register', 'registerWithOtp');
});

Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
    // logout route
    Route::post('logout', [App\Http\Controllers\Api\AuthOtpController::class, 'logout']);

    // Catalog: anyone signed in may read. Writes to products/shops are
    // limited to the owning shop inside the controllers; categories,
    // compositions, brands, regions and shop addresses are managed from the
    // admin panel only, so the API exposes them read-only.
    Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
    Route::get('product/search', [App\Http\Controllers\Api\ProductController::class , 'search']);
    Route::get('product/category/{category_id}', [App\Http\Controllers\Api\ProductController::class , 'getByCategory']);
    Route::get('products/{id}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'index']);
    Route::post('products/{id}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'store']);

    // Compositions routes
    Route::apiResource('compositions', App\Http\Controllers\Api\CompositionController::class)->only(['index', 'show']);

    // Categories routes
    Route::apiResource('categories', App\Http\Controllers\Api\CategoryController::class)->only(['index', 'show']);
    Route::get('/categories/{id}/subcategories', [App\Http\Controllers\Api\CategoryController::class, 'getSubcategories']);

    // Users: only the caller's own profile. Listing/editing other users
    // (phone numbers, passwords) is admin-panel work, not API work.
    Route::get('users/me', [App\Http\Controllers\Api\UserController::class, 'me']);
    Route::put('users/me', [App\Http\Controllers\Api\UserController::class, 'updateMe']);

    // Shops routes
    Route::apiResource('shops', App\Http\Controllers\Api\ShopController::class);

    // Brand routes
    Route::apiResource('brands', App\Http\Controllers\Api\BrandController::class)->only(['index', 'show']);

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
    Route::get('me/notifications', [App\Http\Controllers\Api\UserNotificationController::class, 'index']);
    Route::post('me/notifications/read', [App\Http\Controllers\Api\UserNotificationController::class, 'markAllRead']);
    Route::get('user/orders', [App\Http\Controllers\Api\OrderController::class, 'getUserOrders']);

    // Address routes
    Route::apiResource('addresses', App\Http\Controllers\Api\AddressController::class)->only(['index', 'show']);

    // Regions routes
    Route::apiResource('regions', App\Http\Controllers\Api\RegionController::class)->only(['index', 'show']);
    Route::get('/regions/parent/{parent_id}', [App\Http\Controllers\Api\RegionController::class, 'getByParentId']);
});
