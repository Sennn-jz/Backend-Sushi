<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return response()->json([
            'status' => true,
            'message' => 'List menu berhasil diambil',
            'data' => $menus
        ]);
    }

    public function show($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => false,
                'message' => 'Menu not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $menu
        ]);
    }
}
