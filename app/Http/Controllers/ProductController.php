<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockFilter = $request->query('stock_filter');

        $categories = Category::orderBy('name')->get();

        $products = Product::with('category')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($stockFilter === 'low_stock', function ($query) {
                return $query->lowStock();
            })
            ->when($stockFilter === 'out_of_stock', function ($query) {
                return $query->outOfStock();
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId', 'stockFilter'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'max:255', 'unique:products,barcode'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'default_sale_unit_id' => ['nullable', 'exists:units,id'],
            'default_purchase_unit_id' => ['nullable', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'secondary_units' => ['nullable', 'array'],
            'secondary_units.*.unit_id' => ['required', 'exists:units,id'],
            'secondary_units.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'secondary_units.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'secondary_units.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product = Product::create($validated);

        if (! empty($validated['secondary_units'])) {
            $seenUnits = [];
            foreach ($validated['secondary_units'] as $su) {
                // Don't duplicate base unit or same secondary unit
                if ($su['unit_id'] == $product->unit_id || in_array($su['unit_id'], $seenUnits)) {
                    continue;
                }
                $seenUnits[] = $su['unit_id'];

                $product->secondaryUnits()->create([
                    'unit_id' => $su['unit_id'],
                    'conversion_rate' => $su['conversion_rate'],
                    'sale_price' => ! empty($su['sale_price']) ? $su['sale_price'] : null,
                    'purchase_price' => ! empty($su['purchase_price']) ? $su['purchase_price'] : null,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load(['secondaryUnits.unit']);
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['required', 'string', 'max:255', 'unique:products,barcode,'.$product->id],
            'sku' => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'default_sale_unit_id' => ['nullable', 'exists:units,id'],
            'default_purchase_unit_id' => ['nullable', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'secondary_units' => ['nullable', 'array'],
            'secondary_units.*.unit_id' => ['required', 'exists:units,id'],
            'secondary_units.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'secondary_units.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'secondary_units.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $product->update($validated);

        // Sync secondary units
        $product->secondaryUnits()->delete();

        if (! empty($validated['secondary_units'])) {
            $seenUnits = [];
            foreach ($validated['secondary_units'] as $su) {
                if ($su['unit_id'] == $product->unit_id || in_array($su['unit_id'], $seenUnits)) {
                    continue;
                }
                $seenUnits[] = $su['unit_id'];

                $product->secondaryUnits()->create([
                    'unit_id' => $su['unit_id'],
                    'conversion_rate' => $su['conversion_rate'],
                    'sale_price' => ! empty($su['sale_price']) ? $su['sale_price'] : null,
                    'purchase_price' => ! empty($su['purchase_price']) ? $su['purchase_price'] : null,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->count() > 0 || $product->purchaseItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product with existing purchase or sales records.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
