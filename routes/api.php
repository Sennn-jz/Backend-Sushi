<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =====================================================
// ALL CONTROLLER IMPORTS
// =====================================================
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes - Sushilisious
|--------------------------------------------------------------------------
*/

// =====================================================
// PUBLIC ROUTES (Bisa diakses tanpa login)
// =====================================================
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// Menampilkan menu dan kategori untuk customer umum
Route::get('/menus', [OrderController::class, 'indexMenus']);

// SEARCH MENU BARU
Route::get('/menus/search', [OrderController::class, 'searchMenus']);

Route::get('/categories', [CategoryController::class, 'index']);


// =====================================================
// CUSTOMER PROTECTED ROUTES (Wajib Login)
// =====================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Authentication ---
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Cart Management ---
    Route::get('/cart', [CartController::class, 'index']);

    Route::post('/cart/items', [CartController::class, 'addItem']);

    Route::put('/cart/items/{cartItemId}', [CartController::class, 'updateItem']);

    Route::delete('/cart/items/{cartItemId}', [CartController::class, 'removeItem']);

    Route::delete('/cart', [CartController::class, 'clearCart']);

    // --- Order Actions ---
    Route::post('/orders', [OrderController::class, 'store']);

    Route::post('/cart/checkout', [OrderController::class, 'checkoutFromCart']);

    Route::get('/orders', [OrderController::class, 'myOrders']);

    Route::get('/orders/{orderId}', [OrderController::class, 'show']);

    Route::put('/orders/{orderCode}/cancel', [OrderController::class, 'cancel']);
});


// =====================================================
// ADMIN PROTECTED ROUTES (Wajib Login & Harus Admin)
// =====================================================
Route::middleware(['auth:sanctum', 'ability:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // --- CRUD Menu (Admin) ---
        Route::post('/menus', [AdminController::class, 'storeMenu']);

        Route::put('/menus/{id}', [AdminController::class, 'updateMenu']);

        Route::delete('/menus/{id}', [AdminController::class, 'destroyMenu']);

        // --- CRUD Categories (Admin) ---
        Route::post('/categories', [CategoryController::class, 'store']);

        Route::put('/categories/{id}', [CategoryController::class, 'update']);

        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // --- Manage Orders (Admin) ---
        Route::get('/orders', [AdminController::class, 'getOrders']);

        Route::put('/orders/{id}/confirm', [AdminController::class, 'confirmOrder']);

        Route::put('/orders/{orderCode}/status', [OrderController::class, 'updateStatus']);
    });