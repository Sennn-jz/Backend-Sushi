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

    // ADD TO CART
    public function addItem(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('menu_id', $request->menu_id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity
            ]);
            $item = $existingItem;
        } else {
            $item = CartItem::create([
                'cart_id'  => $cart->id,
                'menu_id'  => $request->menu_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Item berhasil ditambahkan ke cart',
            'data'    => $item->load('menu')
        ], 201);
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
            'status'  => true,
            'message' => 'Quantity diupdate',
            'data'    => $item
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
            'status'  => true,
            'message' => 'Item dihapus dari cart'
        ]);
    }

    // CLEAR CART
    public function clearCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Cart dikosongkan'
        ]);
    }
}