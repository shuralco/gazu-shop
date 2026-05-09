<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class AdminComponent extends Component
{
    public function render()
    {
        // Statistics for dashboard
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalCategories = Category::count();

        // Revenue calculations
        $todayRevenue = Order::whereDate('created_at', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthRevenue = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('quantity', '<', 10)
            ->where('quantity', '>', 0)
            ->limit(5)
            ->get();

        return view('livewire.admin.admin-component', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => $totalUsers,
            'totalCategories' => $totalCategories,
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
