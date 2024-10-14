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

Route::controller(App\Http\Controllers\Api\AuthOtpController::class)->group(function(){
    // OTP Generate route
    Route::post('otp/generate', 'generate');

    // Login with otp route
    Route::post('login', 'loginWithOtp');

    // Register with otp route
    Route::post('register', 'registerWithOtp');

    // logout route
    Route::post('logout', 'logout');
});

Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
    // Products routes
    Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
    Route::get('product/search', [App\Http\Controllers\Api\ProductController::class , 'search']);
    Route::get('product/category/{category_id}', [App\Http\Controllers\Api\ProductController::class , 'getByCategory']);

    // Compositions routes
    Route::apiResource('compositions', App\Http\Controllers\Api\CompositionController::class);

    // Categories routes
    Route::apiResource('categories', App\Http\Controllers\Api\CategoryController::class);
    Route::get('/categories/{id}/subcategories', [App\Http\Controllers\Api\CategoryController::class, 'getSubcategories']);

    // Users routes
    Route::get('users/me', [App\Http\Controllers\Api\UserController::class, 'me']);
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);

    // Shops routes
    Route::apiResource('shops', App\Http\Controllers\Api\ShopController::class);

    // Brand routes
    Route::apiResource('brands', App\Http\Controllers\Api\BrandController::class);

    // Carts routes
    Route::post('cart/add', [App\Http\Controllers\Api\CartController::class, 'addToCart']);
    Route::get('cart', [App\Http\Controllers\Api\CartController::class, 'getCart']);

    // Order routes
    Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class);
    Route::get('user/orders', [App\Http\Controllers\Api\OrderController::class, 'getUserOrders']);

    // Address routes
    Route::apiResource('addresses', App\Http\Controllers\Api\AddressController::class);

    // Regions routes
    Route::apiResource('regions', App\Http\Controllers\Api\RegionController::class);
    Route::get('/regions/parent/{parent_id}', [App\Http\Controllers\Api\RegionController::class, 'getByParentId']);
});