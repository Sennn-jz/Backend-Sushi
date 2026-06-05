<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sushilicious</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col h-full hidden md:flex">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <span class="text-xl font-bold text-slate-800">Sushilicious<span class="text-orange-500">.</span></span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-orange-500 text-white' : 'text-slate-500 hover:bg-gray-50 hover:text-orange-500' }} transition-colors">
                <i class="fas fa-home w-5 text-center mr-3"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>
            
            <a href="{{ route('admin.menus.index') }}" class="flex items-center px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.menus.*') ? 'bg-orange-500 text-white' : 'text-slate-500 hover:bg-gray-50 hover:text-orange-500' }} transition-colors">
                <i class="fas fa-utensils w-5 text-center mr-3"></i>
                <span class="font-medium text-sm">Menus</span>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.orders.*') ? 'bg-orange-500 text-white' : 'text-slate-500 hover:bg-gray-50 hover:text-orange-500' }} transition-colors">
                <i class="fas fa-receipt w-5 text-center mr-3"></i>
                <span class="font-medium text-sm">Orders</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-orange-500 text-white' : 'text-slate-500 hover:bg-gray-50 hover:text-orange-500' }} transition-colors">
                <i class="fas fa-users w-5 text-center mr-3"></i>
                <span class="font-medium text-sm">Users</span>
            </a>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-gray-100">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors">
                    <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                    <span class="font-medium text-sm">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center">
                <button class="md:hidden text-slate-500 hover:text-orange-500 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <div class="flex items-center space-x-4">
                <button class="text-slate-400 hover:text-orange-500 transition-colors relative">
                    <i class="fas fa-bell"></i>
                </button>
                <div class="flex items-center space-x-3 pl-4 border-l border-gray-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-slate-700">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400">Administrator</p>
                    </div>
                    <img class="h-9 w-9 rounded-full bg-orange-100 object-cover border border-gray-200" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'A') }}&background=F97316&color=fff" alt="Avatar">
                </div>
            </div>
        </header>

        <!-- Main section -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 md:p-8">
            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center">
                <i class="fas fa-check-circle mr-3"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
