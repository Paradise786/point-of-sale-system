<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
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

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:adjustment_in,adjustment_out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
        $beforeQty = $product->quantity;
        $qty = $validated['quantity'];

        if ($validated['type'] === 'adjustment_out') {
            if ($qty > $beforeQty) {
                return back()->with('error', "Cannot adjust out {$qty} items. Only {$beforeQty} available in stock.");
            }
            $afterQty = $beforeQty - $qty;
            $product->decrement('quantity', $qty);
        } else {
            $afterQty = $beforeQty + $qty;
            $product->increment('quantity', $qty);
        }

        $reference = 'ADJ-'.date('Ymd').'-'.rand(100, 999);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $validated['type'],
            'quantity' => $qty,
            'before_quantity' => $beforeQty,
            'after_quantity' => $afterQty,
            'reference' => $reference,
            'notes' => $validated['notes'] ?? 'Manual Stock Adjustment',
        ]);

        return back()->with('success', "Stock adjusted successfully. New stock: {$afterQty}");
    }

    public function movements(Request $request): View
    {
        $search = $request->query('search');
        $type = $request->query('type');

        $movements = StockMovement::with('product')
            ->when($search, function ($q, $search) {
                return $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            })
            ->when($type, function ($q, $type) {
                return $q->where('type', $type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock.movements', compact('movements', 'search', 'type'));
    }
}
