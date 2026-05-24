@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
    <p class="text-slate-500">Here's what's happening with your restaurant today.</p>
</div>

<!-- Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Revenue Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
        <div class="rounded-xl bg-orange-100 p-4 text-orange-500 mr-4">
            <i class="fas fa-wallet text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Revenue</p>
            <h3 class="text-2xl font-bold text-slate-800">Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
        </div>
    </div>
    
    <!-- Menus Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
        <div class="rounded-xl bg-orange-100 p-4 text-orange-500 mr-4">
            <i class="fas fa-utensils text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Menus</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ $totalMenus }}</h3>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
        <div class="rounded-xl bg-orange-100 p-4 text-orange-500 mr-4">
            <i class="fas fa-shopping-bag text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Orders</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ $totalOrders }}</h3>
        </div>
    </div>

    <!-- Users Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
        <div class="rounded-xl bg-orange-100 p-4 text-orange-500 mr-4">
            <i class="fas fa-users text-xl"></i>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Users</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ $totalUsers }}</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Chart Section -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-slate-800">Revenue (Last 7 Days)</h2>
        </div>
        <div class="relative h-72 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Latest Orders Section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-slate-800">Latest Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-orange-500 hover:text-orange-600">View All</a>
        </div>
        <div class="space-y-4">
            @forelse($latestOrders as $order)
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-500 font-bold">
                        {{ substr($order->customer_name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">{{ $order->customer_name }}</p>
                        <p class="text-xs text-slate-500">{{ $order->order_code }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $order->status == 'completed' ? 'green' : ($order->status == 'pending' ? 'yellow' : 'gray') }}-100 text-{{ $order->status == 'completed' ? 'green' : ($order->status == 'pending' ? 'yellow' : 'gray') }}-800">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-slate-500 text-sm">No recent orders found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Data from Controller
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Revenue',
                    data: data,
                    backgroundColor: '#F97316', // Orange-500
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        titleFont: { size: 13, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, family: "'Inter', sans-serif" },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#F3F4F6',
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#6B7280',
                            font: { family: "'Inter', sans-serif" },
                            callback: function(value, index, values) {
                                return 'Rp ' + (value / 1000) + 'k';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#6B7280',
                            font: { family: "'Inter', sans-serif" }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
