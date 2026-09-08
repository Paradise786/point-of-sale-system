<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $status = $request->query('status');

        $categories = Category::orderBy('name')->get();

        $query = Product::with('category')
            ->withSum('purchaseItems as total_purchased', 'quantity')
            ->withSum('saleItems as total_sold', 'quantity')
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($q, $categoryId) {
                return $q->where('category_id', $categoryId);
            });

        if ($status === 'low_stock') {
            $query->whereColumn('quantity', '<=', 'alert_quantity')->where('quantity', '>', 0);
        } elseif ($status === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        } elseif ($status === 'in_stock') {
            $query->whereColumn('quantity', '>', 'alert_quantity');
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        // High-level stock metrics
        $totalItemsInStock = Product::sum('quantity');
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();
        $totalStockCostValue = Product::selectRaw('SUM(quantity * purchase_price) as cost_val')->value('cost_val') ?? 0;
        $totalStockRetailValue = Product::selectRaw('SUM(quantity * selling_price) as retail_val')->value('retail_val') ?? 0;

        return view('stock.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'status',
            'totalItemsInStock',
            'lowStockCount',
            'outOfStockCount',
            'totalStockCostValue',
            'totalStockRetailValue'
        ));
    }
}
