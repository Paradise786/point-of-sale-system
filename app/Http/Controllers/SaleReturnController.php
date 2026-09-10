<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();

        $returns = SaleReturn::with(['customer', 'sale', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sale', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            })
            ->when($customerId, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
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

        $totalReturnAmount = SaleReturn::sum('total_amount');
        $totalReturnCount = SaleReturn::count();

        return view('sale_returns.index', compact('returns', 'customers', 'search', 'customerId', 'dateFrom', 'dateTo', 'totalReturnAmount', 'totalReturnCount'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit'])->orderBy('name')->get();
        $sales = Sale::with(['customer', 'items.product.unit', 'items.unit'])->latest()->limit(50)->get();

        $selectedSale = null;
        if ($saleId = $request->query('sale_id')) {
            $selectedSale = Sale::with(['customer', 'items.product.unit', 'items.unit'])->find($saleId);
        }

        return view('sale_returns.create', compact('customers', 'products', 'sales', 'selectedSale'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => ['nullable', 'exists:sales,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'return_date' => ['required', 'date'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:refunded,credited_to_ledger'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $saleReturn = DB::transaction(function () use ($validated) {
            $returnNumber = 'SR-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $saleReturn = SaleReturn::create([
                'return_number' => $returnNumber,
                'sale_id' => $validated['sale_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'return_date' => $validated['return_date'],
                'total_amount' => 0,
                'refund_amount' => $validated['refund_amount'] ?? 0,
                'payment_status' => $validated['payment_status'],
                'note' => $validated['note'] ?? null,
            ]);

            $grandTotal = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = (int) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $conversionRate = (float) $itemData['conversion_rate'];
                $subtotal = $qty * $price;
                $grandTotal += $subtotal;

                $saleReturn->items()->create([
                    'product_id' => $itemData['product_id'],
                    'unit_id' => $itemData['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Increase product base stock
                $baseUnits = (int) round($qty * $conversionRate);
                $product = Product::findOrFail($itemData['product_id']);
                $beforeQty = $product->quantity;
                $afterQty = $beforeQty + $baseUnits;
                $product->update(['quantity' => $afterQty]);

                // Record stock movement (reverse of sale)
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment_in',
                    'quantity' => $baseUnits,
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $afterQty,
                    'reference' => $saleReturn->return_number,
                    'notes' => "Customer Return ({$saleReturn->return_number})",
                ]);
            }

            $saleReturn->update([
                'total_amount' => $grandTotal,
                'refund_amount' => $validated['refund_amount'] ?? $grandTotal,
            ]);

            return $saleReturn;
        });

        return redirect()->route('sale-returns.show', $saleReturn)
            ->with('success', 'Sale return processed successfully and inventory restored.');
    }

    public function show(SaleReturn $saleReturn): View
    {
        $saleReturn->load(['customer', 'sale', 'items.product.category', 'items.unit']);

        return view('sale_returns.show', compact('saleReturn'));
    }
}
