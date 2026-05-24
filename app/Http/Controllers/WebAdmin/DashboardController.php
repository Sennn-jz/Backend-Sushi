<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $revenue = Order::where('status', 'completed')->sum('total_price');
        $totalMenus = Menu::count();
        $totalOrders = Order::count();
        $totalUsers = User::where('role', '!=', 'admin')->count(); // optional

        $latestOrders = Order::with('user')->latest()->take(5)->get();

        // Chart Data: Last 7 days daily revenue
        $chartData = [];
        $chartLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailyRevenue = Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_price');

            $chartLabels[] = Carbon::now()->subDays($i)->format('d M');
            $chartData[] = $dailyRevenue;
        }

        return view('admin.dashboard', compact(
            'revenue', 'totalMenus', 'totalOrders', 'totalUsers', 'latestOrders', 'chartLabels', 'chartData'
        ));
    }
}
