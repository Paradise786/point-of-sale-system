<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $purchases = Purchase::with(['vendor', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases', 'search'));
    }

    public function create(): View
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();

        return view('purchases.create', compact('vendors', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'purchase_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $referenceNo = 'PO-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $purchase = Purchase::create([
                'reference_no' => $referenceNo,
                'vendor_id' => $validated['vendor_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $totalAmount,
                'status' => 'received',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['purchase_price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'subtotal' => $subtotal,
                ]);

                // Stock automatically increases
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->increment('quantity', $item['quantity']);
                    $afterQty = $beforeQty + $item['quantity'];

                    // Record Stock Movement History
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'quantity' => $item['quantity'],
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $referenceNo,
                        'notes' => 'Stock in via Purchase Invoice',
                    ]);

                    // Also update purchase price to reflect latest cost
                    $product->update(['purchase_price' => $item['purchase_price']]);
                }
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase order created successfully and stock updated.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['vendor', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }
}
