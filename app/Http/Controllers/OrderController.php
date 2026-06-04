<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =====================================================
    // GUEST / PUBLIC ROUTES
    // =====================================================

    // GET /api/menus
    public function indexMenus()
    {
        $menus = Menu::where('is_available', true)->get();

        return response()->json([
            'status'  => true,
            'message' => 'Daftar menu Sushilisious',
            'data'    => $menus
        ]);
    }

    // =====================================================
    // SEARCH MENU
    // GET /api/menus/search?keyword=salmon
    // =====================================================
    public function searchMenus(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string'
        ]);

        $keyword = $request->keyword;

        $menus = Menu::where('is_available', true)
            ->where(function ($query) use ($keyword) {
                $query->where('id', $keyword)
                      ->orWhere('name', 'like', '%' . $keyword . '%');
            })
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Hasil pencarian menu',
            'data'    => $menus
        ]);
    }

    // =====================================================
    // PATH A: Bayar Langsung (tanpa cart)
    // POST /api/orders
    // =====================================================
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'address'          => 'required|string',
            'payment_method'   => 'required|in:cash,transfer,qris',
            'items'            => 'required|array|min:1',
            'items.*.menu_id'  => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {

            $total = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];
                $total += $subtotal;
                $itemsData[] = [
                    'menu_id'  => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price'    => $menu->price,
                ];
            }

            $order = Order::create([
                'user_id'       => $request->user()->id,
                'order_code'    => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $request->customer_name,
                'address'       => $request->address,
                'total_price'   => $total,
                'status'        => 'pending',
            ]);

            foreach ($itemsData as $itemData) {
                OrderItem::create(array_merge(
                    $itemData,
                    ['order_id' => $order->id]
                ));
            }

            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $request->payment_method,
                'status'         => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'status'      => true,
                'message'     => 'Pesanan berhasil dibuat',
                'order_code'  => $order->order_code,
                'total_price' => $order->total_price,
                'data'        => $order->load('items.menu', 'payment'),
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // PATH B: Checkout dari Cart
    // POST /api/cart/checkout
    // =====================================================
    public function checkoutFromCart(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'address'        => 'required|string',
            'payment_method' => 'required|in:cash,transfer,qris',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->with('items.menu')
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Cart kosong, tidak bisa checkout',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $total = $cart->items->sum(
                fn($item) => $item->menu->price * $item->quantity
            );

            $order = Order::create([
                'user_id'       => $request->user()->id,
                'order_code'    => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $request->customer_name,
                'address'       => $request->address,
                'total_price'   => $total,
                'status'        => 'pending',
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id'  => $cartItem->menu_id,
                    'quantity' => $cartItem->quantity,
                    'price'    => $cartItem->menu->price,
                ]);
            }

            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $request->payment_method,
                'status'         => 'pending',
            ]);

            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'status'      => true,
                'message'     => 'Checkout berhasil',
                'order_code'  => $order->order_code,
                'total_price' => $order->total_price,
                'data'        => $order->load('items.menu', 'payment'),
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Gagal checkout: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // CUSTOMER HISTORY & ACTIONS
    // =====================================================

    public function myOrders(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.menu', 'payment')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $orders,
        ]);
    }

    public function show(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->with('items.menu', 'payment')
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $order,
        ]);
    }

    public function cancel(Request $request, $orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Pesanan tidak dapat dibatalkan karena status sudah ' . $order->status,
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'Pesanan berhasil dibatalkan',
            'data'    => [
                'order_code' => $order->order_code,
                'status'     => $order->status
            ]
        ]);
    }

    // GET /api/orders/history/all
    public function history(Request $request)
    {
        $history = Order::where('user_id', $request->user()->id)
            ->with('items.menu', 'payment')
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Riwayat transaksi user berhasil diambil',
            'data'    => $history
        ]);
    }

    // =====================================================
    // ADMIN ACTIONS
    // =====================================================

    public function getOrders()
    {
        $orders = Order::with('items.menu', 'payment', 'user')
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Seluruh riwayat transaksi berhasil diambil oleh Admin',
            'data'    => $orders
        ]);
    }

    public function updateStatus(Request $request, $orderCode)
    {
        $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled'
        ]);

        $order = Order::where('order_code', $orderCode)->firstOrFail();

        $order->update(['status' => $request->status]);

        return response()->json([
            'status'  => true,
            'message' => "Status pesanan {$orderCode} berhasil diperbarui menjadi {$request->status}",
            'data'    => [
                'order_code' => $order->order_code,
                'status'     => $order->status,
                'updated_at' => $order->updated_at
            ]
        ]);
    }
}