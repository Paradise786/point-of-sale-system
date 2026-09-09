<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
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
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories', 'customers', 'products'));
    }

    /**
     * Search products for POS barcode scanning and instant lookup.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q');

        $products = Product::with('category')
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
            'payment_method' => ['required', 'in:cash,card,bank_transfer'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
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

                if ($product->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for '{$product->name}'. Available: {$product->quantity}, Requested: {$item['quantity']}."],
                    ]);
                }

                $price = (float) $product->selling_price;
                $subtotal = $price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsToProcess[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $paidAmount = (float) $validated['paid_amount'];
            if ($paidAmount < $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => ['Paid amount (Rs. '.number_format($paidAmount, 2).') cannot be less than total amount (Rs. '.number_format($totalAmount, 2).').'],
                ]);
            }

            $changeAmount = $paidAmount - $totalAmount;
            $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(4));

            // 2. Create Sale
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note'] ?? null,
            ]);

            // 3. Create Sale Items and Decrement Stock
            $processedItems = [];
            foreach ($itemsToProcess as $entry) {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $entry['product']->id,
                    'quantity' => $entry['quantity'],
                    'price' => $entry['price'],
                    'subtotal' => $entry['subtotal'],
                ]);

                // Reduce stock
                $beforeQty = $entry['product']->quantity;
                $entry['product']->decrement('quantity', $entry['quantity']);
                $afterQty = $beforeQty - $entry['quantity'];

                // Record Stock Movement History
                StockMovement::create([
                    'product_id' => $entry['product']->id,
                    'type' => 'sale',
                    'quantity' => $entry['quantity'],
                    'before_quantity' => $beforeQty,
                    'after_quantity' => $afterQty,
                    'reference' => $invoiceNumber,
                    'notes' => 'Stock out via POS Sale',
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
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'change_amount' => $sale->change_amount,
                    'items' => $processedItems,
                ],
                'receipt_url' => route('sales.receipt', $sale->id),
                'invoice_url' => route('sales.show', $sale->id),
            ]);
        });
    }
}
