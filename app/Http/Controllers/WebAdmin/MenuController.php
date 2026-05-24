<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::latest()->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Menyimpan gambar ke folder storage/app/public/menus
            $path = $request->file('image')->store('menus', 'public');
            // Cukup simpan path-nya saja (misal: menus/namafile.png) ke database
            $validated['image_url'] = $path;
        }

        $validated['is_available'] = $request->has('is_available');

        Menu::create($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada sebelum upload yang baru (opsional tapi disarankan)
            if ($menu->image_url && !str_starts_with($menu->image_url, 'http')) {
                Storage::disk('public')->delete($menu->image_url);
            }

            // Simpan gambar baru
            $path = $request->file('image')->store('menus', 'public');
            $validated['image_url'] = $path;
        }

        $validated['is_available'] = $request->has('is_available');

        $menu->update($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        // Hapus file gambar dari storage sebelum menghapus data dari database
        if ($menu->image_url && !str_starts_with($menu->image_url, 'http')) {
            Storage::disk('public')->delete($menu->image_url);
        }

        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}