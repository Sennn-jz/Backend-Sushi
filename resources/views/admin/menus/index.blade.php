@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Menus</h1>
        <p class="text-slate-500">Manage your restaurant's menu items.</p>
    </div>
    <a href="{{ route('admin.menus.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-xl transition-colors flex items-center shadow-sm">
        <i class="fas fa-plus mr-2"></i> Add Menu
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-white">
        <form action="{{ route('admin.menus.index') }}" method="GET" class="relative max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search menu by name..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 text-sm transition-colors bg-gray-50 focus:bg-white">
            <i class="fas fa-search absolute left-3.5 top-3 text-gray-400"></i>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Item</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($menus as $menu)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-6 flex items-center min-w-[250px]">
                        @php
                            // Mengambil field gambar (antisipasi kalau namanya image atau image_url)
                            $namaGambar = $menu->image ?? $menu->image_url;
                            
                            // Logika penentuan URL Gambar
                            if (empty($namaGambar)) {
                                $urlFinal = 'https://images.unsplash.com/photo-1611143669185-af224c5e3252?w=120&auto=format&fit=crop&q=60';
                            } elseif (str_starts_with($namaGambar, 'http')) {
                                $urlFinal = $namaGambar;
                            } else {
                                // Bersihkan string jika ada sisa tulisan 'storage/' dari database
                                $cleanPath = ltrim($namaGambar, '/');
                                if (str_starts_with($cleanPath, 'storage/')) {
                                    $cleanPath = substr($cleanPath, 8);
                                }
                                $urlFinal = asset('storage/' . $cleanPath);
                            }
                        @endphp

                        <img src="{{ $urlFinal }}" alt="{{ $menu->name }}" class="w-12 h-12 rounded-lg object-cover mr-4 border border-gray-200">
                        <div>
                            <p class="font-bold text-slate-800">{{ $menu->name }}</p>
                            <p class="text-xs text-slate-500 truncate w-48">{{ $menu->description }}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        @php
                            $colorClass = match(strtolower($menu->category)) {
                                'sushi' => 'bg-red-100 text-red-600',
                                'sashimi' => 'bg-blue-100 text-blue-600',
                                'ramen' => 'bg-amber-100 text-amber-600',
                                'drink' => 'bg-cyan-100 text-cyan-600',
                                'dessert' => 'bg-pink-100 text-pink-600',
                                default => 'bg-gray-100 text-slate-600'
                            };
                        @endphp
                        <span class="{{ $colorClass }} py-1 px-3 rounded-full text-xs font-medium">{{ $menu->category ?? 'Uncategorized' }}</span>
                    </td>
                    <td class="py-4 px-6 font-medium text-slate-800">
                        Rp {{ number_format($menu->price, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-6">
                        @if($menu->is_available)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-600 border border-orange-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span>
                                Available
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500 mr-1.5"></span>
                                Unavailable
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-right whitespace-nowrap">
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="text-slate-400 hover:text-orange-500 transition-colors mx-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this menu?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors mx-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-slate-500">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-utensils text-2xl text-gray-400"></i>
                        </div>
                        <p>No menus found. Click 'Add Menu' to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection