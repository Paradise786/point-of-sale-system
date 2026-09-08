<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with key performance indicators.
     */
    public function index(): View
    {
        $today = Carbon::today();

        $totalSales = (float) Sale::sum('total_amount');
        $todaySales = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $todayOrders = Sale::whereDate('created_at', $today)->count();
        $totalPurchases = (float) Purchase::sum('total_amount');

        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalCategories = Category::count();
        $lowStockCount = Product::lowStock()->count();

        $recentSales = Sale::with('customer')
            ->latest()
            ->take(6)
            ->get();

        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('quantity', 'asc')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'totalSales',
            'todaySales',
            'todayOrders',
            'totalPurchases',
            'totalProducts',
            'totalCustomers',
            'totalCategories',
            'lowStockCount',
            'recentSales',
            'lowStockProducts'
        ));
    }
}
