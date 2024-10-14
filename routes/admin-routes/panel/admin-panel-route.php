<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-z]{2}'],
], function () {
    
    Route::prefix('admin')->group(function(){
        Route::get('/dashboard', [App\Http\Controllers\AdminControllers\Dashboard\DashboardController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/export',[App\Http\Controllers\UserControllers\UserController::class, 'export'])->name('export')->middleware(['auth:admin']);
        
        Route::resources([
            '/{categoryType}/category' => App\Http\Controllers\AdminControllers\Category\CategoryController::class,
            '/attribute' => App\Http\Controllers\AdminControllers\Attribute\AttributeController::class,
            '/user' => App\Http\Controllers\AdminControllers\User\UserController::class,
            '/address' => App\Http\Controllers\AdminControllers\Address\AddressController::class,
            '/region' => App\Http\Controllers\AdminControllers\Region\RegionController::class,
            '/shop' => App\Http\Controllers\AdminControllers\Shop\ShopController::class,
            '/product' => App\Http\Controllers\AdminControllers\Product\ProductController::class,
            '/message' => App\Http\Controllers\AdminControllers\Message\MessageController::class,
            '/admin' => App\Http\Controllers\AdminControllers\Admin\AdminController::class,
            '/role' => App\Http\Controllers\AdminControllers\Role\RoleController::class,
            '/permission' => App\Http\Controllers\AdminControllers\Permission\PermissionController::class,
            '/cart' => App\Http\Controllers\AdminControllers\Cart\CartController::class,
        ]);
    });
});