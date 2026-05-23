<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Menu;

class FavoriteController extends Controller
{
    // =====================================================
    // GET FAVORITES
    // =====================================================
    public function index(Request $request)
    {
        $favorites = Favorite::with('menu')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil favorite menu',
            'data' => $favorites
        ]);
    }

    // =====================================================
    // ADD FAVORITE
    // =====================================================
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id'
        ]);

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('menu_id', $request->menu_id)
            ->first();

        if ($favorite) {
            return response()->json([
                'status' => false,
                'message' => 'Menu sudah ada di favorite'
            ]);
        }

        $favorite = Favorite::create([
            'user_id' => $request->user()->id,
            'menu_id' => $request->menu_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menambahkan favorite',
            'data' => $favorite
        ]);
    }

    // =====================================================
    // DELETE FAVORITE
    // =====================================================
    public function destroy(Request $request, $menu_id)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('menu_id', $menu_id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'status' => false,
                'message' => 'Favorite tidak ditemukan'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'status' => true,
            'message' => 'Favorite berhasil dihapus'
        ]);
    }
}