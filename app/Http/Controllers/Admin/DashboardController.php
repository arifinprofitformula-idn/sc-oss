<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Statistics
        $totalUsers = User::count();
        $activeChannels = User::role('SILVERCHANNEL')->where('status', 'ACTIVE')->count();
        
        // Orders Stats
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', 'PAID')
            ->sum('total_amount');
            
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'PAID')
            ->sum('total_amount');

        // Recent Orders
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 
            'activeChannels', 
            'todayOrders', 
            'todayRevenue', 
            'monthlyRevenue',
            'recentOrders'
        ));
    }
}
