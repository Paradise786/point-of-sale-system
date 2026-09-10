<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Unit;
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
        $vendorId = $request->query('vendor_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $vendors = Vendor::orderBy('name')->get();

        $purchases = Purchase::with(['vendor', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($vendorId, function ($query, $vendorId) {
                return $query->where('vendor_id', $vendorId);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                return $query->whereDate('purchase_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                return $query->whereDate('purchase_date', '<=', $dateTo);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalPurchasesAmount = Purchase::sum('total_amount');
        $totalPurchasesCount = Purchase::count();

        return view('purchases.index', compact('purchases', 'vendors', 'search', 'vendorId', 'dateFrom', 'dateTo', 'totalPurchasesAmount', 'totalPurchasesCount'));
    }

    public function create(Request $request): View
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->orderBy('name')->get();
        $pendingOrders = PurchaseOrder::with(['vendor', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
            ->pending()
            ->latest()
            ->get();

        $selectedPo = null;
        $poId = $request->query('purchase_order_id') ?? $request->query('po_id');
        if ($poId) {
            $selectedPo = PurchaseOrder::with(['vendor', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
                ->find($poId);
        }

        return view('purchases.create', compact('vendors', 'products', 'pendingOrders', 'selectedPo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'purchase_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['purchase_order_id'])) {
            $linkedPo = PurchaseOrder::find($validated['purchase_order_id']);
            if ($linkedPo && $linkedPo->isConverted()) {
                return back()->withInput()->with('error', 'This Purchase Order has already been converted into a Purchase Invoice.');
            }
        }

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $referenceNo = 'PI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $purchase = Purchase::create([
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'reference_no' => $referenceNo,
                'vendor_id' => $validated['vendor_id'],
                'purchase_date' => $validated['purchase_date'] ?? now()->toDateString(),
                'total_amount' => $totalAmount,
                'status' => 'received',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['purchase_price'];
                $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;
                $baseQuantity = $item['quantity'] * $conversionRate;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                    'purchase_price' => $item['purchase_price'],
                    'subtotal' => $subtotal,
                ]);

                // Stock automatically increases in BASE UNITS
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->increment('quantity', $baseQuantity);
                    $afterQty = $beforeQty + $baseQuantity;

                    // Record Stock Movement History
                    $unitModel = ! empty($item['unit_id']) ? Unit::find($item['unit_id']) : null;
                    $unitLabel = $unitModel ? $unitModel->short_code : ($product->unit ? $product->unit->short_code : 'units');

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'quantity' => $baseQuantity,
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $referenceNo,
                        'notes' => "Stock in: {$item['quantity']} {$unitLabel} ({$baseQuantity} base units) via Purchase",
                    ]);
                }
            }

            // Mark Purchase Order as converted if applicable
            if (! empty($validated['purchase_order_id'])) {
                $po = PurchaseOrder::find($validated['purchase_order_id']);
                if ($po) {
                    $po->update([
                        'status' => 'converted',
                        'converted_purchase_id' => $purchase->id,
                    ]);
                }
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase invoice created successfully and inventory updated.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['vendor', 'purchaseOrder', 'items.product.unit', 'items.unit']);

        return view('purchases.show', compact('purchase'));
    }
}
