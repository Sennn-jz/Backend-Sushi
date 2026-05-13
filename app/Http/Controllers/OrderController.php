<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        $request->validate([
            'payment_method' => 'required|string'
        ]);

        if (!$cart || empty($cart->items)) {
            return response()->json(['status' => false, 'message' => 'Cart is empty'], 400);
        }

        $totalPrice = array_reduce($cart->items, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $orderToken = 'ORD-' . Str::random(10);

        $order = Order::create([
            'user_id' => Auth::id(),
            'items' => $cart->items,
            'total_price' => $totalPrice,
            'order_token' => $orderToken,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
        
        // Save payment method
        \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'payment_time' => null,
        ]);

        OrderHistory::create([
            'order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'pending',
            'updated_by' => Auth::id(),
        ]);

        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pesanan berhasil dibuat',
            'data' => [
                'order_id' => $order->id,
                'order_token' => $orderToken,
                'total_price' => $totalPrice,
                'status' => 'pending'
            ]
        ], 201);
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        
        $data = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'status' => $order->status,
                'total_price' => $order->total_price,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $order = Order::where('_id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    public function updatePayment(Request $request, $id)
    {
        $order = Order::where('_id', $id)->where('user_id', Auth::id())->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->payment_status = 'paid';
        $order->save();
        
        $payment = \App\Models\Payment::where('order_id', $order->id)->first();
        if ($payment) {
            $payment->payment_status = 'paid';
            $payment->payment_time = now();
            $payment->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil'
        ]);
    }
}
