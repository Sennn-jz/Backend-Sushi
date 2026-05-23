<?php

namespace App\Http\Controllers;

use App\Models\{Cart, CartItem, Menu};
use Illuminate\Http\Request;

class CartController extends Controller
{
    // GET /api/cart — lihat isi cart (Sekarang otomatis menampilkan notes/add-on)
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

    // POST /api/cart/items — tambah item ke cart + simpan data Add-on
    public function addItem(Request $request)
    {
        // 1. Validasi input (kolom notes diizinkan kosong jika user tidak pilih add-on)
        $request->validate([
            'menu_id'  => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string', // <-- Validasi baru untuk add-on
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // 2. Cek apakah menu dengan add-on (notes) yang SAMA PERSIS sudah ada di keranjang?
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('menu_id', $request->menu_id)
            ->where('notes', $request->notes) // <-- Memastikan add-on nya sama
            ->first();

        if ($existingItem) {
            // Jika menu & variasi add-on sama, tinggal tambahkan jumlahnya (quantity)
            $existingItem->increment('quantity', $request->quantity);
            $item = $existingItem->fresh();
        } else {
            // Jika menu baru atau add-on nya berbeda, buat baris baru di database
            $item = CartItem::create([
                'cart_id'  => $cart->id,
                'menu_id'  => $request->menu_id,
                'quantity' => $request->quantity,
                'notes'    => $request->notes, // <-- Menyimpan data teks add-on ke DB
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