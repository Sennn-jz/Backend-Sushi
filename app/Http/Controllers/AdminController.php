<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function storeMenu(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'is_available' => 'boolean',
        ]);

        $menu = Menu::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Menu berhasil ditambahkan',
            'data' => $menu
        ], 201);
    }

    public function updateMenu(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['status' => false, 'message' => 'Menu not found'], 404);
        }

        $menu->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Menu updated successfully',
            'data' => $menu
        ]);
    }

    public function destroyMenu($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['status' => false, 'message' => 'Menu not found'], 404);
        }

        $menu->delete();

        return response()->json([
            'status' => true,
            'message' => 'Menu berhasil dihapus'
        ]);
    }

    public function getOrders()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }

    // =====================================================
    // CONFIRM ORDER (Revisi Admin Dashboard No. 3)
    // PUT /api/admin/orders/{id}/confirm
    // =====================================================
    public function confirmOrder(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // Memastikan pesanan hanya bisa dikonfirmasi jika statusnya masih pending
        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Pesanan tidak bisa dikonfirmasi karena statusnya sudah ' . $order->status
            ], 400);
        }

        // Mengubah status pesanan menjadi 'confirmed' sesuai revisi admin
        $order->status = 'confirmed';
        $order->save();

        // Otomatis update status pembayaran menjadi sukses jika ada relasi payment
        if ($order->payment) {
            $order->payment->update([
                'status' => 'success'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Pesanan berhasil dikonfirmasi oleh Admin (Status: confirmed)',
            'data' => $order->load('payment')
        ]);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,done,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order status updated',
            'data' => $order
        ]);
    }
}