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
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Admin\AdminAuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Sushilisious
|--------------------------------------------------------------------------
*/

// =====================================================
// PUBLIC ROUTES (Bisa diakses tanpa login)
// =====================================================

// --- Authentication ---
Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/admin/login', [AdminAuthController::class, 'login']);


// --- Public Menu & Category ---
Route::get('/menus', [OrderController::class, 'indexMenus']);

Route::get('/menus/search', [OrderController::class, 'searchMenus']);

Route::get('/categories', [CategoryController::class, 'index']);


// =====================================================
// CUSTOMER PROTECTED ROUTES (Wajib Login)
// =====================================================
Route::middleware('auth:sanctum')->group(function () {

    // =================================================
    // AUTH & NOTIFICATIONS
    // =================================================
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Endpoint Profile: Menampilkan data user sekaligus notifikasinya
    Route::get('/user/profile', [AuthController::class, 'profile']);

    // Endpoint Update Profile: Mengubah pengaturan akun user
    Route::put('/user/profile/update', [AuthController::class, 'updateProfile']);

    // ENDPOINT NOTIFIKASI
    Route::get('/notifications', [AuthController::class, 'getNotifications']);


    // =================================================
    // CART MANAGEMENT
    // =================================================
    Route::get('/cart', [CartController::class, 'index']);

    // Menambahkan menu langsung ke keranjang dari home screen
    Route::post('/cart/items', [CartController::class, 'addItem']);

    // Mengubah jumlah item / add-on
    Route::put('/cart/items/{cartItemId}', [CartController::class, 'updateItem']);

    // Menghapus item keranjang
    Route::delete('/cart/items/{cartItemId}', [CartController::class, 'removeItem']);

    // Clear keranjang
    Route::delete('/cart', [CartController::class, 'clearCart']);

    // =================================================
    // COUPON / DISKON
    // =================================================
    Route::post('/coupon/redeem', [CouponController::class, 'redeem']);


    // =================================================
    // ORDER ACTIONS
    // =================================================
    Route::post('/orders', [OrderController::class, 'store']);

    // Checkout dari cart
    Route::post('/cart/checkout', [OrderController::class, 'checkoutFromCart']);

    Route::get('/orders', [OrderController::class, 'myOrders']);

    Route::get('/orders/{orderId}', [OrderController::class, 'show']);

    Route::put('/orders/{orderCode}/cancel', [OrderController::class, 'cancel']);

    // ORDER HISTORY
    Route::get('/orders/history/all', [OrderController::class, 'history']);


    // =================================================
    // FAVORITE MENU
    // =================================================
    Route::post('/favorites', [FavoriteController::class, 'store']);

    Route::get('/favorites', [FavoriteController::class, 'index']);

    Route::delete('/favorites/{menu_id}', [FavoriteController::class, 'destroy']);
});


// =====================================================
// ADMIN PROTECTED ROUTES (Wajib Login & Admin)
// =====================================================
Route::middleware(['auth:sanctum', 'ability:admin'])
    ->prefix('admin')
    ->group(function () {

        // =============================================
        // ADMIN AUTH
        // =============================================
        Route::post('/logout', [AdminAuthController::class, 'logout']);


        // =============================================
        // CRUD MENU
        // =============================================
        Route::post('/menus', [AdminController::class, 'storeMenu']);

        Route::put('/menus/{id}', [AdminController::class, 'updateMenu']);

        Route::delete('/menus/{id}', [AdminController::class, 'destroyMenu']);


        // =============================================
        // CRUD CATEGORY
        // =============================================
        Route::post('/categories', [CategoryController::class, 'store']);

        Route::put('/categories/{id}', [CategoryController::class, 'update']);

        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


        // =============================================
        // MANAGE ORDERS
        // =============================================
        Route::get('/orders', [OrderController::class, 'getOrders']);

        // Konfirmasi order
        Route::put('/orders/{id}/confirm', [AdminController::class, 'confirmOrder']);

        // Update status order
        Route::put('/orders/{orderCode}/status', [OrderController::class, 'updateStatus']);
    });