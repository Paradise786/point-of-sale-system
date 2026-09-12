<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
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
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();

        $orders = SaleOrder::with(['customer', 'items.product'])
            ->when($search, function ($q, $search) {
                return $q->where('so_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($customerId, function ($q, $customerId) {
                return $q->where('customer_id', $customerId);
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

        return view('sale_orders.index', compact('orders', 'customers', 'search', 'status', 'customerId', 'dateFrom', 'dateTo'));
    }

    public function create(): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->where('quantity', '>', 0)->orderBy('name')->get();

        return view('sale_orders.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
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

            $soNumber = 'SO-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $order = SaleOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Stock DOES NOT change while pending
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;

                SaleOrderItem::create([
                    'sale_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
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
        $saleOrder->load(['customer', 'items.product.unit', 'items.unit']);

        return view('sale_orders.show', compact('saleOrder'));
    }

    public function convertToInvoice(Request $request, SaleOrder $saleOrder): RedirectResponse
    {
        if ($saleOrder->isConverted()) {
            return back()->with('error', 'This sale order has already been converted to an invoice.');
        }

        return redirect()->route('sales.create', ['sale_order_id' => $saleOrder->id])
            ->with('info', "Sale Order {$saleOrder->so_number} loaded into Invoice Create form. Review details and complete the sale.");
    }
}
