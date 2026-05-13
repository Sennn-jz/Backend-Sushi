<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{id}', [MenuController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/payment', [OrderController::class, 'updatePayment']);

    // Admin Routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::post('/menus', [AdminController::class, 'storeMenu']);
        Route::put('/menus/{id}', [AdminController::class, 'updateMenu']);
        Route::delete('/menus/{id}', [AdminController::class, 'destroyMenu']);
        
        Route::get('/orders', [AdminController::class, 'getOrders']);
        Route::put('/orders/{id}/confirm', [AdminController::class, 'confirmOrder']);
        Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    });
});
