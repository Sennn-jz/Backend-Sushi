@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Users</h1>
        <p class="text-slate-500">Manage registered users and administrators.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Account Name</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Username</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-6 flex items-center">
                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200 mr-4" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=F97316&color=fff" alt="Avatar">
                        <div>
                            <p class="font-bold text-slate-800">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-sm font-medium text-slate-600">
                        {{ '@' . ($user->username ?? 'none') }}
                    </td>
                    <td class="py-4 px-6">
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                                <i class="fas fa-shield-alt mr-1.5 text-[10px]"></i> Admin
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                <i class="fas fa-user mr-1.5 text-[10px]"></i> Customer
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-sm text-slate-500">
                        {{ $user->created_at->format('d M Y, H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-slate-500">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-users text-2xl text-gray-400"></i>
                        </div>
                        <p>No users found in the system.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
