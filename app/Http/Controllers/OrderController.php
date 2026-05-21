<?php
namespace App\Http\Controllers;
use App\Models\{Cart, CartItem, Order, OrderItem, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // =====================================================
    // PATH A: Bayar Langsung (tanpa cart)
    // POST /api/orders
    // =====================================================
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'address'        => 'required|string',
            'payment_method' => 'required|in:cash,transfer,qris',
            'items'          => 'required|array|min:1',
            'items.*.menu_id'  => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // 1. Hitung total
            $total = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $menu = \App\Models\Menu::findOrFail($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];
                $total += $subtotal;

                $itemsData[] = [
                    'menu_id'  => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price'    => $menu->price,  // snapshot harga
                ];
            }

            // 2. Buat order + generate order_code unik
            $order = Order::create([
                'user_id'       => $request->user()->id,
                'order_code'    => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $request->customer_name,
                'address'       => $request->address,
                'total_price'   => $total,
                'status'        => 'pending',
            ]);

            // 3. Simpan order items
            foreach ($itemsData as $itemData) {
                OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));
            }

            // 4. Simpan payment
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
            // 1. Hitung total dari cart
            $total = $cart->items->sum(
                fn($item) => $item->menu->price * $item->quantity
            );

            // 2. Buat order + generate order_code unik
            $order = Order::create([
                'user_id'       => $request->user()->id,
                'order_code'    => 'ORD-' . strtoupper(Str::random(8)),
                'customer_name' => $request->customer_name,
                'address'       => $request->address,
                'total_price'   => $total,
                'status'        => 'pending',
            ]);

            // 3. Pindahkan cart items → order items (snapshot harga)
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id'  => $cartItem->menu_id,
                    'quantity' => $cartItem->quantity,
                    'price'    => $cartItem->menu->price,
                ]);
            }

            // 4. Simpan payment
            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $request->payment_method,
                'status'         => 'pending',
            ]);

            // 5. Kosongkan cart setelah checkout
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

    // Lihat detail pesanan milik user
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

    // Lihat semua pesanan milik user
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
}
