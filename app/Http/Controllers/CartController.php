<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // GET CART
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        $items = $cart->items()->with('menu')->get();

        $total = $items->sum(function ($item) {
            return $item->menu->price * $item->quantity;
        });

        return response()->json([
            'status' => true,
            'data' => [
                'cart_id' => $cart->id,
                'items' => $items,
                'total' => $total,
            ],
        ]);
    }

    // =========================
    // ADD TO CART (DEBUG MODE)
    // =========================
    public function addItem(Request $request)
    {
        // 🔥 DEBUG CHECK (INI PENTING)
        dd([
            'request_data' => $request->all(),
            'user' => $request->user(),
            'headers' => $request->headers->all()
        ]);
    }

    // UPDATE ITEM
    public function updateItem(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        $item->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quantity diupdate',
            'data' => $item
        ]);
    }

    // DELETE ITEM
    public function removeItem(Request $request, $cartItemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item dihapus dari cart'
        ]);
    }
}