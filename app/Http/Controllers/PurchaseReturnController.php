<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $vendorId = $request->query('vendor_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $vendors = Vendor::orderBy('name')->get();

        $returns = PurchaseReturn::with(['vendor', 'purchase', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchase', function ($q) use ($search) {
                        $q->where('reference_no', 'like', "%{$search}%");
                    });
            })
            ->when($vendorId, function ($query, $vendorId) {
                return $query->where('vendor_id', $vendorId);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                return $query->whereDate('return_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                return $query->whereDate('return_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalReturnAmount = PurchaseReturn::sum('total_amount');
        $totalReturnCount = PurchaseReturn::count();

        return view('purchase_returns.index', compact('returns', 'vendors', 'search', 'vendorId', 'dateFrom', 'dateTo', 'totalReturnAmount', 'totalReturnCount'));
    }

    public function create(Request $request): View
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->orderBy('name')->get();
        $purchases = Purchase::with(['vendor', 'items.product.unit', 'items.unit'])->latest()->limit(50)->get();

        $selectedPurchase = null;
        if ($purchaseId = $request->query('purchase_id')) {
            $selectedPurchase = Purchase::with(['vendor', 'items.product.unit', 'items.unit'])->find($purchaseId);
        }

        return view('purchase_returns.create', compact('vendors', 'products', 'purchases', 'selectedPurchase'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'return_date' => ['required', 'date'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseReturn = DB::transaction(function () use ($validated) {
            $returnNumber = 'PR-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $purchaseReturn = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_id' => $validated['purchase_id'] ?? null,
                'vendor_id' => $validated['vendor_id'],
                'return_date' => $validated['return_date'],
                'total_amount' => 0,
                'refund_amount' => $validated['refund_amount'] ?? 0,
                'note' => $validated['note'] ?? null,
            ]);

            $grandTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $conversionRate = (float) $itemData['conversion_rate'];
                $subtotal = $qty * $price;
                $grandTotal += $subtotal;

                $purchaseReturn->items()->create([
                    'product_id' => $itemData['product_id'],
                    'unit_id' => $itemData['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Decrement product base stock
                $baseUnits = (int) round($qty * $conversionRate);
                $product = Product::findOrFail($itemData['product_id']);
                $beforeQty = $product->quantity;
                $afterQty = max(0, $beforeQty - $baseUnits);
                $product->update(['quantity' => $afterQty]);

                // Record stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment_out',
                    'quantity' => $baseUnits,
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $afterQty,
                    'reference' => $purchaseReturn->return_number,
                    'notes' => "Vendor Return ({$purchaseReturn->return_number})",
                ]);
            }

            $purchaseReturn->update([
                'total_amount' => $grandTotal,
                'refund_amount' => $validated['refund_amount'] ?? $grandTotal,
            ]);

            if (! empty($validated['purchase_id'])) {
                Purchase::where('id', $validated['purchase_id'])->update([
                    'payment_status' => 'return',
                ]);
            }

            return $purchaseReturn;
        });

        return redirect()->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return processed successfully and inventory adjusted.');
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['vendor', 'purchase', 'items.product.category', 'items.unit']);

        return view('purchase_returns.show', compact('purchaseReturn'));
    }
}
