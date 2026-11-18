<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get overall system statistics
        $totalOrders = Order::count();
        
        // Calculate total revenue (accepting both 'Completed' or lower case 'completed', excluding 'Cancelled')
        $totalRevenue = Order::where('status', '!=', 'Cancelled')
            ->selectRaw('SUM(subtotal - discount + delivery_fee) as total')
            ->value('total') ?? 0;

        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        // 2. Get monthly revenue statistics for the current year (PostgreSQL compatible)
        $monthlyRevenue = Order::select(
                DB::raw('SUM(subtotal - discount + delivery_fee) as revenue'),
                DB::raw('EXTRACT(MONTH FROM created_at) as month')
            )
            ->where('status', '!=', 'Cancelled')
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->pluck('revenue', 'month');

        $chartData = array_fill(1, 12, 0);
        foreach ($monthlyRevenue as $month => $revenue) {
            $chartData[(int)$month] = $revenue;
        }

        // 3. Get the latest 5 orders for quick review
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard.index', compact(
            'totalOrders', 
            'totalRevenue', 
            'totalCustomers', 
            'totalProducts', 
            'chartData', 
            'recentOrders'
        ));
    }
}