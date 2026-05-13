<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return response()->json([
                'status' => true, 
                'data' => [
                    'items' => [], 
                    'total_price' => 0
                ]
            ]);
        }

        $items = [];
        $totalPrice = 0;
        if ($cart->items) {
            foreach ($cart->items as $item) {
                $menu = \App\Models\Menu::find($item['menu_id']);
                if ($menu) {
                    $items[] = [
                        'menu_id' => $menu->id,
                        'menu_name' => $menu->name,
                        'quantity' => $item['quantity'],
                        'price' => $menu->price,
                    ];
                    $totalPrice += ($menu->price * $item['quantity']);
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'items' => $items,
                'total_price' => $totalPrice,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $menu = \App\Models\Menu::find($request->menu_id);
        if (!$menu) {
            return response()->json(['status' => false, 'message' => 'Menu not found'], 404);
        }

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        
        $items = $cart->items ?? [];
        $menuId = $request->menu_id;
        
        $found = false;
        foreach ($items as &$item) {
            if ($item['menu_id'] === $menuId) {
                $item['quantity'] += $request->quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $items[] = [
                'menu_id' => $menuId,
                'quantity' => $request->quantity,
                'price' => $menu->price,
            ];
        }

        $cart->items = $items;
        $cart->save();

        return response()->json(['status' => true, 'message' => 'Menu berhasil ditambahkan ke cart']);
    }

    public function destroy($menu_id)
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return response()->json(['status' => false, 'message' => 'Cart not found'], 404);
        }

        $items = array_filter($cart->items, function ($item) use ($menu_id) {
            return $item['menu_id'] !== $menu_id;
        });

        $cart->items = array_values($items);
        $cart->save();

        return response()->json(['status' => true, 'message' => 'Item removed from cart']);
    }
}
