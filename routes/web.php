<?php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',[App\Http\Controllers\UserControllers\UserController::class, 'goToMainPage'])->name('goToMainPage');

// There is no customer web login (customers sign in in the app by SMS code);
// "login" exists so framework redirects for guests land on the admin login.
Route::get('/login', function () {
    return redirect()->route('admin.login', app()->getLocale());
})->name('login');

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-z]{2}'],
], function () {
    Route::get('/sowgatly', [App\Http\Controllers\UserControllers\UserController::class, 'mainPage'])->name('main-page');
});

require __DIR__ . '/admin-routes/auth/admin-auth-route.php';
require __DIR__ . '/admin-routes/panel/admin-panel-route.php';

