@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Products Inventory</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage products, pricing, barcodes, and stock levels.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('pos.access'))
                <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping text-emerald-400"></i>
                    <span>Open POS</span>
                </a>
            @endif
            @if(auth()->user()?->hasPermission('products.create'))
                <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add Product</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name or scan barcode..." 
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="flex items-center gap-2">
                <select name="stock_filter" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Stock Statuses</option>
                    <option value="low_stock" {{ $stockFilter === 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock</option>
                    <option value="out_of_stock" {{ $stockFilter === 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                    Filter
                </button>
                @if (!empty($search) || !empty($categoryId) || !empty($stockFilter))
                    <a href="{{ route('products.index') }}" class="px-2 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition" title="Clear Filters">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Product</th>
                        <th class="px-5 py-3.5">Barcode</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Cost Price</th>
                        <th class="px-5 py-3.5">Selling Price</th>
                        <th class="px-5 py-3.5">Stock</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800">{{ $product->name }}</div>
                                @if ($product->description)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $product->description }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-700 flex items-center gap-1.5">
                                <img src="{{ route('products.barcode', $product) }}" alt="Barcode" class="h-12 inline-block mr-2" />
                                <span>{{ $product->barcode }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-700">
                                    {{ $product->category->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500 font-medium">
                                Rs. {{ number_format($product->purchase_price, 2) }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-800">
                                Rs. {{ number_format($product->selling_price, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-black {{ $product->quantity <= 0 ? 'text-rose-600' : ($product->is_low_stock ? 'text-amber-600' : 'text-slate-800') }}">
                                    {{ $product->quantity }} {{ $product->unit->short_code ?? 'pcs' }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">Min: {{ $product->alert_quantity }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($product->quantity <= 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                                    </span>
                                @elseif ($product->is_low_stock)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if(auth()->user()?->hasPermission('products.edit'))
                                        <a href="{{ route('products.edit', $product) }}" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasPermission('products.delete'))
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-box-open text-4xl text-slate-200 mb-3"></i>
                                    <p class="font-medium text-sm">No products found matching the criteria.</p>
                                    <a href="{{ route('products.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                        Add your first product
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
