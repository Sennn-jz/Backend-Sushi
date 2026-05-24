<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAdmin\AuthController;
use App\Http\Controllers\WebAdmin\DashboardController;
use App\Http\Controllers\WebAdmin\MenuController;
use App\Http\Controllers\WebAdmin\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Auth Routes
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('menus', MenuController::class);
        
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });
});
 