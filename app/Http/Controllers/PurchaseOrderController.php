<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $vendorId = $request->query('vendor_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $vendors = Vendor::orderBy('name')->get();

        $orders = PurchaseOrder::with(['vendor', 'items.product'])
            ->when($search, function ($q, $search) {
                return $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($vendorId, function ($q, $vendorId) {
                return $q->where('vendor_id', $vendorId);
            })
            ->when($status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($dateFrom, function ($q, $dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q, $dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase_orders.index', compact('orders', 'vendors', 'search', 'status', 'vendorId', 'dateFrom', 'dateTo'));
    }

    public function create(): View
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->orderBy('name')->get();

        return view('purchase_orders.create', compact('vendors', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $poNumber = 'PO-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $order = PurchaseOrder::create([
                'po_number' => $poNumber,
                'vendor_id' => $validated['vendor_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending', // Stock DOES NOT change while pending
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order generated with status Pending. (Stock remains unchanged until converted to Invoice).');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['vendor', 'items.product.unit', 'items.unit']);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function convertToInvoice(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->isConverted()) {
            return back()->with('error', 'This purchase order has already been converted to an invoice.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $refNo = 'PI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $purchase = Purchase::create([
                'purchase_order_id' => $purchaseOrder->id,
                'reference_no' => $refNo,
                'vendor_id' => $purchaseOrder->vendor_id,
                'purchase_date' => now()->toDateString(),
                'total_amount' => $purchaseOrder->total_amount,
                'status' => 'received',
                'note' => "Converted from Purchase Order: {$purchaseOrder->po_number}",
            ]);

            foreach ($purchaseOrder->items as $item) {
                $conversionRate = (float) ($item->conversion_rate ?? 1.0);
                $baseQuantity = $item->quantity * $conversionRate;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'unit_id' => $item->unit_id,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $item->quantity,
                    'base_quantity' => $baseQuantity,
                    'purchase_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Increase physical stock in BASE UNITS
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->increment('quantity', $baseQuantity);
                    $afterQty = $beforeQty + $baseQuantity;

                    $unitLabel = $item->unit ? $item->unit->short_code : ($product->unit ? $product->unit->short_code : 'units');

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'quantity' => $baseQuantity,
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $refNo,
                        'notes' => "Stock in: {$item->quantity} {$unitLabel} ({$baseQuantity} base units) from PO: {$purchaseOrder->po_number}",
                    ]);
                }
            }

            $purchaseOrder->update([
                'status' => 'converted',
                'converted_purchase_id' => $purchase->id,
            ]);
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} successfully converted to Purchase Invoice & Stock updated!");
    }
}
