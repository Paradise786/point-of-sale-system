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

        $orders = PurchaseOrder::with(['vendor', 'items.product'])
            ->when($search, function ($q, $search) {
                return $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase_orders.index', compact('orders', 'search', 'status'));
    }

    public function create(): View
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::with('unit')->orderBy('name')->get();

        return view('purchase_orders.create', compact('vendors', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
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
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $item['product_id'],
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
        $purchaseOrder->load(['vendor', 'items.product']);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function convertToInvoice(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'This purchase order has already been converted to an invoice.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $refNo = 'PI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $purchase = Purchase::create([
                'reference_no' => $refNo,
                'vendor_id' => $purchaseOrder->vendor_id,
                'purchase_date' => now()->toDateString(),
                'total_amount' => $purchaseOrder->total_amount,
                'status' => 'received',
                'note' => "Converted from Purchase Order: {$purchaseOrder->po_number}",
            ]);

            foreach ($purchaseOrder->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'purchase_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Increase physical stock
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->increment('quantity', $item->quantity);
                    $afterQty = $beforeQty + $item->quantity;

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'quantity' => $item->quantity,
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $refNo,
                        'notes' => "Stock in from converted PO: {$purchaseOrder->po_number}",
                    ]);

                    $product->update(['purchase_price' => $item->unit_price]);
                }
            }

            $purchaseOrder->update([
                'status' => 'received',
                'converted_purchase_id' => $purchase->id,
            ]);
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} successfully converted to Purchase Invoice & Stock updated!");
    }
}
