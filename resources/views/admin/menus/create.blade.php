@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <div class="flex items-center space-x-3 mb-2">
        <a href="{{ route('admin.menus.index') }}" class="text-slate-400 hover:text-orange-500 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Add New Menu</h1>
    </div>
    <p class="text-slate-500 pl-8">Create a new item for your restaurant's menu.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-3xl">
    <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">Menu Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="e.g. Salmon Nigiri">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Category</label>
                <select name="category" required class="block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                    <option value="">Select a category</option>
                    <option value="Sushi" {{ old('category') == 'Sushi' ? 'selected' : '' }}>Sushi</option>
                    <option value="Sashimi" {{ old('category') == 'Sashimi' ? 'selected' : '' }}>Sashimi</option>
                    <option value="Ramen" {{ old('category') == 'Ramen' ? 'selected' : '' }}>Ramen</option>
                    <option value="Drink" {{ old('category') == 'Drink' ? 'selected' : '' }}>Drink</option>
                    <option value="Dessert" {{ old('category') == 'Dessert' ? 'selected' : '' }}>Dessert</option>
                </select>
                @error('category') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Price (Rp)</label>
                <input type="number" name="price" value="{{ old('price') }}" required min="0" class="block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="e.g. 50000">
                @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                <textarea name="description" rows="3" required class="block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="Brief description of the menu...">{{ old('description') }}</textarea>
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">Menu Image</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer" onclick="document.getElementById('file-upload').click()">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-image text-gray-400 text-3xl mb-3"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-orange-500 hover:text-orange-600 focus-within:outline-none">
                                <span>Upload a file</span>
                                <input id="file-upload" name="image" type="file" class="sr-only" accept="image/*" onchange="document.getElementById('file-name').textContent = this.files[0] ? 'Selected: ' + this.files[0].name : 'PNG, JPG, WEBP up to 2MB'; document.getElementById('file-name').classList.replace('text-gray-500', 'text-orange-500');">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p id="file-name" class="text-xs text-gray-500 mt-2">PNG, JPG, WEBP up to 2MB</p>
                    </div>
                </div>
                @error('image') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-1 md:col-span-2 flex items-center">
                <input id="is_available" name="is_available" type="checkbox" checked class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 rounded">
                <label for="is_available" class="ml-2 block text-sm text-slate-700">
                    Menu is available for order
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.menus.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                Save Menu
            </button>
        </div>
    </form>
</div>
@endsection
