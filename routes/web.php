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

Route::get('/api-docs', function () {
    return view('l5-swagger::index');
});

Route::get('/otp', [App\Http\Controllers\UserControllers\UserController::class, 'send'])->name('otp');

Route::get('/Esca6585', [App\Http\Controllers\UserControllers\UserController::class, 'resume'])->name('resume');

Route::get('/',[App\Http\Controllers\UserControllers\UserController::class, 'goToMainPage'])->name('goToMainPage');

Route::get('/login', function(){
    return redirect()->route('login', app()->getlocale());
} )->name('login');

Route::get('/tm/email/verify', [App\Http\Controllers\Auth\VerificationController::class, 'show'])->name('verification.notice');
Route::get('/tm/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/tm/email/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])->name('verification.resend');

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => '[a-z]{2}'],
], function () {

    Route::get('/sowgatly', [App\Http\Controllers\UserControllers\UserController::class, 'mainPage'])->name('main-page');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'profile'])->name('home');
    Route::get('/email', [App\Http\Controllers\HomeController::class, 'email']);

    Route::get('/profile', [App\Http\Controllers\HomeController::class, 'profile'])->name('profile');
    Route::get('/profile/password/change', [App\Http\Controllers\HomeController::class, 'passwordChange'])->name('profile.password.change');
    Route::post('/profile/password/change', [App\Http\Controllers\HomeController::class, 'passwordChangeEdit'])->name('profile.password.edit');
    Route::get('/profile/cart', [App\Http\Controllers\HomeController::class, 'cart'])->name('profile.cart');
    Route::get('/profile/application', [App\Http\Controllers\HomeController::class, 'application'])->name('profile.application');
    Route::get('/profile/application/create', [App\Http\Controllers\HomeController::class, 'applicationCreate'])->name('profile.application.create');
    Route::get('/profile/application/create/{id}/{section}', [App\Http\Controllers\HomeController::class, 'applicationCreateSection'])->name('profile.application.create.section');
    Route::get('/profile/application/{code_number}/docx', [App\Http\Controllers\HomeController::class, 'docx'])->name('profile.docx');
    Route::get('/profile/{code_number}/docx', [App\Http\Controllers\HomeController::class, 'docx']);
    Route::get('/profile/letterhead', [App\Http\Controllers\HomeController::class, 'letterhead'])->name('profile.letterhead');
    Route::post('/profile/letterhead/edit', [App\Http\Controllers\HomeController::class, 'letterheadEdit'])->name('profile.letterhead.edit');
    Route::post('/profile/edit', [App\Http\Controllers\HomeController::class, 'profileEdit'])->name('profile.edit');
    Route::post('/profile/form', [App\Http\Controllers\HomeController::class, 'form'])->name('profile.form');

    Auth::routes(
        [
            'login' => true,
            'register' => true,
        ]
    );
    
});

Auth::routes(
    [
        'login' => false,
        'register' => false,
        'reset' => true,
    ]
);

require __DIR__ . '/admin-routes/auth/admin-auth-route.php';
require __DIR__ . '/admin-routes/panel/admin-panel-route.php';

