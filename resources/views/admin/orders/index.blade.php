@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Orders History</h1>
        <p class="text-slate-500">View and manage all customer orders.</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Code</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Amount</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-6 font-medium text-slate-800">
                        {{ $order->order_code }}
                    </td>
                    <td class="py-4 px-6 flex items-center">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 font-bold mr-3 text-xs">
                            {{ substr($order->customer_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ $order->customer_name }}</p>
                            <p class="text-xs text-slate-500">{{ $order->user->email ?? 'Guest' }}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-sm text-slate-600">
                        {{ $order->created_at->format('d M Y, H:i') }}
                    </td>
                    <td class="py-4 px-6 font-bold text-slate-800">
                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-6">
                        @php
                            $colors = [
                                'pending' => 'yellow',
                                'confirmed' => 'blue',
                                'completed' => 'green',
                                'cancelled' => 'red'
                            ];
                            $color = $colors[$order->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="inline-flex items-center space-x-2">
                            @csrf
                            @method('PUT')
                            <select name="status" class="text-xs border border-gray-200 rounded-lg p-1.5 focus:outline-none focus:ring-1 focus:ring-orange-500" onchange="this.form.submit()">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-500">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-receipt text-2xl text-gray-400"></i>
                        </div>
                        <p>No orders found yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->hasPages())
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
