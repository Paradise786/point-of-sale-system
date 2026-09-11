<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Show the POS terminal screen.
     */
    /**
     * Show the POS terminal screen.
     */
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['category', 'unit', 'secondaryUnits.unit'])
            ->orderBy('name')
            ->get();

        $pendingSaleOrders = SaleOrder::with(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
            ->pending()
            ->latest()
            ->get();

        $selectedSo = null;
        $soId = $request->query('sale_order_id') ?? $request->query('so_id');
        if ($soId) {
            $selectedSo = SaleOrder::with(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit'])
                ->find($soId);
        }

        return view('pos.index', compact('categories', 'customers', 'products', 'pendingSaleOrders', 'selectedSo'));
    }

    /**
     * Search products for POS barcode scanning and instant lookup.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q');

        $products = Product::with(['category', 'unit', 'secondaryUnits.unit'])
            ->when($query, function ($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->take(20)
            ->get();

        return response()->json($products);
    }

    /**
     * Process POS sale checkout transaction.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_order_id' => ['nullable', 'exists:sale_orders,id'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,online'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            // Check if linked Sale Order is already converted
            if (! empty($validated['sale_order_id'])) {
                $linkedOrder = SaleOrder::find($validated['sale_order_id']);
                if ($linkedOrder && $linkedOrder->isConverted()) {
                    throw ValidationException::withMessages([
                        'sale_order_id' => ['This Sale Order has already been converted into a Sale Invoice.'],
                    ]);
                }
            }

            $totalAmount = 0;
            $itemsToProcess = [];

            // 1. Verify stock availability and lock rows
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$item['id']} not found."],
                    ]);
                }

                $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;
                $baseQuantity = $item['quantity'] * $conversionRate;

                if ($product->quantity < $baseQuantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for '{$product->name}'. Available: {$product->quantity} base units, Requested: {$baseQuantity} base units ({$item['quantity']} packaging units)."],
                    ]);
                }

                // Determine price
                $price = isset($item['price']) && $item['price'] > 0
                    ? (float) $item['price']
                    : (float) $product->selling_price * $conversionRate;

                $subtotal = $price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsToProcess[] = [
                    'product' => $product,
                    'unit_id' => $item['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $item['quantity'],
                    'base_quantity' => $baseQuantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $paidAmount = (float) $validated['paid_amount'];
            if ($paidAmount < $totalAmount && empty($validated['customer_id'])) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Please select a Customer for Unpaid or Partially Paid invoices so the remaining balance (Rs. '.number_format($totalAmount - $paidAmount, 2).') is recorded in their ledger.'],
                ]);
            }

            $actualPaid = min($paidAmount, $totalAmount);
            $changeAmount = max(0, $paidAmount - $totalAmount);
            $dueAmount = max(0, $totalAmount - $paidAmount);
            $paymentStatus = Sale::computePaymentStatus($paidAmount, $totalAmount);
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(4));

            // 2. Create Sale
            $sale = Sale::create([
                'sale_order_id' => $validated['sale_order_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $actualPaid,
                'due_amount' => $dueAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'note' => $validated['note'] ?? null,
            ]);

            // 3. Create Sale Items and Decrement Stock in BASE UNITS
            $processedItems = [];
            foreach ($itemsToProcess as $entry) {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $entry['product']->id,
                    'unit_id' => $entry['unit_id'],
                    'conversion_rate' => $entry['conversion_rate'],
                    'quantity' => $entry['quantity'],
                    'base_quantity' => $entry['base_quantity'],
                    'price' => $entry['price'],
                    'subtotal' => $entry['subtotal'],
                ]);

                // Reduce stock in base units
                $beforeQty = $entry['product']->quantity;
                $entry['product']->decrement('quantity', $entry['base_quantity']);
                $afterQty = $beforeQty - $entry['base_quantity'];

                $unitModel = ! empty($entry['unit_id']) ? Unit::find($entry['unit_id']) : null;
                $unitLabel = $unitModel ? $unitModel->short_code : ($entry['product']->unit ? $entry['product']->unit->short_code : 'units');

                // Record Stock Movement History
                StockMovement::create([
                    'product_id' => $entry['product']->id,
                    'type' => 'sale',
                    'quantity' => $entry['base_quantity'],
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $afterQty,
                    'reference' => $invoiceNumber,
                    'notes' => "Stock out: {$entry['quantity']} {$unitLabel} ({$entry['base_quantity']} base units) via POS Sale",
                ]);

                $processedItems[] = [
                    'name' => $entry['product']->name,
                    'barcode' => $entry['product']->barcode,
                    'quantity' => $entry['quantity'],
                    'price' => $entry['price'],
                    'subtotal' => $entry['subtotal'],
                    'remaining_stock' => $afterQty,
                ];
            }

            // Mark Sale Order as converted if applicable
            if (! empty($validated['sale_order_id'])) {
                $so = SaleOrder::find($validated['sale_order_id']);
                if ($so) {
                    $so->update([
                        'status' => 'converted',
                        'converted_sale_id' => $sale->id,
                    ]);
                }
            }

            $sale->load('customer');

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'date' => $sale->created_at->format('d M Y, h:i A'),
                    'customer' => $sale->customer_display_name,
                    'payment_method' => ucfirst(str_replace('_', ' ', $sale->payment_method)),
                    'payment_status' => $sale->payment_status,
                    'payment_status_label' => $sale->payment_status_label,
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'due_amount' => $sale->due_amount,
                    'change_amount' => $sale->change_amount,
                    'items' => $processedItems,
                ],
                'receipt_url' => route('sales.receipt', $sale->id),
                'invoice_url' => route('sales.show', $sale->id),
            ]);
        });
    }
}
