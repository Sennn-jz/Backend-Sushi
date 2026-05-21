<?php
namespace App\Http\Controllers;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::where('is_available', true)->get();

        return response()->json([
            'status'  => true,
            'message' => 'Daftar menu',
            'data'    => $menus,
        ]);
    }
}
