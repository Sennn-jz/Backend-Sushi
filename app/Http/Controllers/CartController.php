<?php
namespace App\Http\Controllers;
use App\Models\{Cart, CartItem, Menu};
use Illuminate\Http\Request;

class CartController extends Controller
{
    // GET /api/cart — lihat isi cart
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $items = $cart->items()->with('menu')->get();

        $total = $items->sum(fn($item) => $item->menu->price * $item->quantity);

        return response()->json([
            'status' => true,
            'data'   => [
                'cart_id' => $cart->id,
                'items'   => $items,
                'total'   => $total,
            ],
        ]);
    }

    // POST /api/cart/items — tambah item ke cart
    public function addItem(Request $request)
    {
        $request->validate([
            'menu_id'  => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // Kalau item sudah ada di cart, tambah qty-nya
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('menu_id', $request->menu_id)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $request->quantity);
            $item = $existingItem->fresh();
        } else {
            $item = CartItem::create([
                'cart_id'  => $cart->id,
                'menu_id'  => $request->menu_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Item ditambahkan ke cart',
            'data'    => $item,
        ], 201);
    }

    // PUT /api/cart/items/{cartItemId} — update qty item
    public function updateItem(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        $item->update(['quantity' => $request->quantity]);

        return response()->json([
            'status'  => true,
            'message' => 'Quantity diupdate',
            'data'    => $item,
        ]);
    }

    // DELETE /api/cart/items/{cartItemId} — hapus item dari cart
    public function removeItem(Request $request, $cartItemId)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Item dihapus dari cart',
        ]);
    }
}
