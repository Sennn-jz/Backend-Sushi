<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;

// === PUBLIC ROUTES ===
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// Menu (semua user bisa lihat)
Route::get('/menus', [MenuController::class, 'index']);


// === PROTECTED ROUTES — USER ===
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // =========================
    // CART
    // =========================
    Route::get('/cart',                       [CartController::class, 'index']);
    Route::post('/cart/items',               [CartController::class, 'addItem']);
    Route::put('/cart/items/{cartItemId}',   [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItemId}',[CartController::class, 'removeItem']);

    // =========================
    // ORDERS
    // =========================
    Route::post('/orders',                [OrderController::class, 'store']);
    Route::post('/cart/checkout',         [OrderController::class, 'checkoutFromCart']);

    Route::get('/orders',                 [OrderController::class, 'myOrders']);
    Route::get('/orders/{orderId}',       [OrderController::class, 'show']);

    // CANCEL ORDER
    Route::put('/orders/{orderCode}/cancel', [OrderController::class, 'cancel']);
});


// === PROTECTED ROUTES — ADMIN ===
// Middleware 'auth:sanctum' + cek ability 'admin'
Route::middleware(['auth:sanctum', 'ability:admin'])
    ->prefix('admin')
    ->group(function () {

    Route::post('/logout', [AdminAuthController::class, 'logout']);

    // =========================
    // CRUD MENU
    // =========================
    Route::post('/menus', [AdminController::class, 'storeMenu']);
    Route::put('/menus/{id}', [AdminController::class, 'updateMenu']);
    Route::delete('/menus/{id}', [AdminController::class, 'destroyMenu']);

    // =========================
    // MANAGE ORDERS
    // =========================
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::put('/orders/{id}/confirm', [AdminController::class, 'confirmOrder']);
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
});