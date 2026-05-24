<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sushilicious Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800">Sushilicious<span class="text-orange-500">.</span></h1>
            <p class="text-slate-500 mt-2">Welcome back! Please login to your account.</p>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" required class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="admin@sushilicious.com">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:bg-white focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2.5 rounded-xl transition-colors">
                Sign In
            </button>
        </form>
    </div>

</body>
</html>
