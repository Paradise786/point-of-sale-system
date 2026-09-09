<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SaleOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $orders = SaleOrder::with(['customer', 'items.product'])
            ->when($search, function ($q, $search) {
                return $q->where('so_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sale_orders.index', compact('orders', 'search', 'status'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();

        return view('sale_orders.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
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

            $soNumber = 'SO-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $order = SaleOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Stock DOES NOT change while pending
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                SaleOrderItem::create([
                    'sale_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return redirect()->route('sale-orders.index')
            ->with('success', 'Customer Sale Order created with status Pending. (Stock remains unchanged until converted to Invoice).');
    }

    public function show(SaleOrder $saleOrder): View
    {
        $saleOrder->load(['customer', 'items.product']);

        return view('sale_orders.show', compact('saleOrder'));
    }

    public function convertToInvoice(Request $request, SaleOrder $saleOrder): RedirectResponse
    {
        if ($saleOrder->status === 'confirmed') {
            return back()->with('error', 'This sale order has already been converted to an invoice.');
        }

        $paymentMethod = $request->input('payment_method', 'cash');

        // Check stock availability
        foreach ($saleOrder->items as $item) {
            if ($item->product->quantity < $item->quantity) {
                return back()->with('error', "Insufficient stock for '{$item->product->name}'. Available: {$item->product->quantity}, Requested: {$item->quantity}");
            }
        }

        DB::transaction(function () use ($saleOrder, $paymentMethod) {
            $invoiceNumber = 'SI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $saleOrder->customer_id,
                'total_amount' => $saleOrder->total_amount,
                'paid_amount' => $saleOrder->total_amount,
                'change_amount' => 0,
                'payment_method' => $paymentMethod,
                'note' => "Converted from Sale Order: {$saleOrder->so_number}",
            ]);

            foreach ($saleOrder->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                // Reduce stock now upon invoice creation
                $beforeQty = $item->product->quantity;
                $item->product->decrement('quantity', $item->quantity);
                $afterQty = $beforeQty - $item->quantity;

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'type' => 'sale',
                    'quantity' => $item->quantity,
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $afterQty,
                    'reference' => $invoiceNumber,
                    'notes' => "Stock out from converted SO: {$saleOrder->so_number}",
                ]);
            }

            $saleOrder->update([
                'status' => 'confirmed',
                'converted_sale_id' => $sale->id,
            ]);
        });

        return redirect()->route('sales.index')
            ->with('success', "Sale Order {$saleOrder->so_number} successfully converted to Sale Invoice & Stock updated!");
    }
}
