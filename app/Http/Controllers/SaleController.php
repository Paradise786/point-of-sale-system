<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status');
        $paymentMethod = $request->query('payment_method');
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();

        $sales = Sale::with(['customer', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($customerId, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', $paymentStatus);
            })
            ->when($paymentMethod, function ($query, $paymentMethod) {
                return $query->where('payment_method', $paymentMethod);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalRevenue = Sale::sum('total_amount');
        $totalPaid = Sale::sum('paid_amount');
        $totalDue = Sale::sum('due_amount');
        $totalOrders = Sale::count();

        return view('sales.index', compact('sales', 'customers', 'search', 'customerId', 'paymentStatus', 'paymentMethod', 'dateFrom', 'dateTo', 'totalRevenue', 'totalPaid', 'totalDue', 'totalOrders'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->where('quantity', '>', 0)->orderBy('name')->get();
        $pendingOrders = SaleOrder::with(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
            ->pending()
            ->latest()
            ->get();

        $selectedSo = null;
        $soId = $request->query('sale_order_id') ?? $request->query('so_id') ?? $request->query('order_id');
        if ($soId) {
            $selectedSo = SaleOrder::with(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
                ->find($soId);
        }

        return view('sales.create', compact('customers', 'products', 'pendingOrders', 'selectedSo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_order_id' => ['nullable', 'exists:sale_orders,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,bank_transfer,cheque,online'],
            'note' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'extra_field_one' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['sale_order_id'])) {
            $linkedSo = SaleOrder::find($validated['sale_order_id']);
            if ($linkedSo && $linkedSo->isConverted()) {
                return back()->withInput()->with('error', 'This Sale Order has already been converted into a Sale Invoice.');
            }
        }

        // Check stock availability in base units
        foreach ($validated['items'] as $item) {
            $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;
            $baseRequired = $item['quantity'] * $conversionRate;
            $product = Product::find($item['product_id']);

            if ($product && $product->quantity < $baseRequired) {
                return back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->quantity} base units, Requested: {$baseRequired} base units.");
            }
        }

        $sale = DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
            $actualPaid = min($paidAmount, $totalAmount);
            $changeAmount = max(0, $paidAmount - $totalAmount);
            $dueAmount = max(0, $totalAmount - $paidAmount);
            $paymentStatus = Sale::computePaymentStatus($paidAmount, $totalAmount);
            $invoiceNumber = 'SI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $sale = Sale::create([
                'sale_order_id' => $validated['sale_order_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'paid_amount' => $actualPaid,
                'due_amount' => $dueAmount,
                'change_amount' => $changeAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'note' => $validated['note'] ?? null,
                'description' => $validated['description'] ?? null,
                'extra_field_one' => $validated['extra_field_one'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;
                $baseQuantity = $item['quantity'] * $conversionRate;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                    'price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                // Reduce inventory in BASE UNITS
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->decrement('quantity', $baseQuantity);
                    $afterQty = $beforeQty - $baseQuantity;

                    $unitModel = ! empty($item['unit_id']) ? Unit::find($item['unit_id']) : null;
                    $unitLabel = $unitModel ? $unitModel->short_code : ($product->unit ? $product->unit->short_code : 'units');

                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'sale',
                        'quantity' => $baseQuantity,
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $invoiceNumber,
                        'notes' => "Stock out: {$item['quantity']} {$unitLabel} ({$baseQuantity} base units) via Sale Invoice",
                    ]);
                }
            }

            // Mark linked Sale Order as converted if applicable
            if (! empty($validated['sale_order_id'])) {
                $so = SaleOrder::find($validated['sale_order_id']);
                if ($so) {
                    $so->update([
                        'status' => 'converted',
                        'converted_sale_id' => $sale->id,
                    ]);
                }
            }

            return $sale;
        });

        // Redirect directly to thermal print receipt
        return redirect()->route('sales.receipt', $sale)
            ->with('success', 'Sale Invoice created successfully and inventory updated.');
    }

    public function fetchFromOrder(SaleOrder $saleOrder): JsonResponse
    {
        $saleOrder->load(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit']);

        return response()->json([
            'success' => true,
            'order' => $saleOrder,
        ]);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'saleOrder', 'items.product.category', 'items.unit']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['customer', 'items.product.unit', 'items.unit']);

        return view('sales.receipt', compact('sale'));
    }

    public function printPreview(Sale $sale): View
    {
        return $this->receipt($sale);
    }
}
