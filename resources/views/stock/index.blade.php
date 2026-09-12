@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Stock & Inventory Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Real-time stock balance calculated from total purchases and sales.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('stock.movements') || auth()->user()?->hasPermission('stock.view'))
                <a href="{{ route('stock.movements') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Movement History</span>
                </a>
            @endif
            @if(auth()->user()?->hasPermission('stock.adjust'))
                <button type="button" onclick="openAdjustmentModal()" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Manual Adjustment</span>
                </button>
            @endif
            @if(auth()->user()?->hasPermission('purchases.create'))
                <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Restock / New Purchase</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Stock KPI Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Stock Units</p>
                <p class="text-xl font-black text-slate-800">{{ number_format($totalItemsInStock) }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $lowStockCount > 0 ? 'bg-amber-50 text-amber-600 animate-pulse' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Low Stock Warnings</p>
                <p class="text-xl font-black {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-800' }}">{{ $lowStockCount }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $outOfStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-xl">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Out of Stock</p>
                <p class="text-xl font-black {{ $outOfStockCount > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $outOfStockCount }}</p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-vault"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Stock Retail Value</p>
                <p class="text-xl font-black text-slate-800">Rs. {{ number_format($totalStockRetailValue, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Tabs -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm space-y-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 overflow-x-auto text-xs font-bold">
            <a href="{{ route('stock.index') }}" 
               class="px-4 py-2 rounded-lg transition {{ empty($status) ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                All Products
            </a>
            <a href="{{ route('stock.index', ['status' => 'in_stock']) }}" 
               class="px-4 py-2 rounded-lg transition {{ $status === 'in_stock' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                In Stock (Healthy)
            </a>
            <a href="{{ route('stock.index', ['status' => 'low_stock']) }}" 
               class="px-4 py-2 rounded-lg transition {{ $status === 'low_stock' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                ⚠️ Low Stock (<= Alert)
            </a>
            <a href="{{ route('stock.index', ['status' => 'out_of_stock']) }}" 
               class="px-4 py-2 rounded-lg transition {{ $status === 'out_of_stock' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                🚫 Out of Stock
            </a>
        </div>

        <!-- Search and Category Filters -->
        <form action="{{ route('stock.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @if (!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <div class="relative sm:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by product name or barcode..." 
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div class="flex items-center gap-2">
                <select name="category_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                    Apply
                </button>
                @if (!empty($search) || !empty($categoryId))
                    <a href="{{ route('stock.index', array_filter(['status' => $status])) }}" class="px-2 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition" title="Clear">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Product</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5 text-center">Purchased</th>
                        <th class="px-5 py-3.5 text-center">Sold</th>
                        <th class="px-5 py-3.5 text-center">Available Stock</th>
                        <th class="px-5 py-3.5">Alert Level</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-800 block">{{ $product->name }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $product->barcode }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-600">
                                {{ $product->category->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-600">
                                {{ $product->total_purchased ?? 0 }}
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-600">
                                {{ $product->total_sold ?? 0 }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-base font-black {{ $product->quantity <= 0 ? 'text-rose-600' : ($product->is_low_stock ? 'text-amber-600' : 'text-slate-800') }}">
                                    {{ $product->quantity }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                &le; {{ $product->alert_quantity }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($product->quantity <= 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span> Out of Stock
                                    </span>
                                @elseif ($product->is_low_stock)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('purchases.create') }}" class="px-3 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Restock
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <p class="font-medium text-sm">No inventory products found matching the criteria.</p>
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

<!-- Stock Adjustment Modal -->
<div id="adjustmentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-800">Manual Stock Adjustment</h3>
                <p class="text-xs text-slate-500">Correct physical inventory discrepancy (+ or -)</p>
            </div>
            <button type="button" onclick="closeAdjustmentModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('stock.adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Select Product *</label>
                <select name="product_id" id="adj_product_id" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">Choose a product...</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (Current: {{ $p->quantity }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Adjustment Type *</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer text-xs font-semibold">
                        <input type="radio" name="type" value="adjustment_in" checked class="text-emerald-600 focus:ring-emerald-500">
                        <span>➕ Add Stock (+)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer text-xs font-semibold">
                        <input type="radio" name="type" value="adjustment_out" class="text-rose-600 focus:ring-rose-500">
                        <span>➖ Deduct Stock (-)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Quantity *</label>
                <input type="number" min="1" name="quantity" required placeholder="e.g. 2"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Reason / Notes</label>
                <input type="text" name="notes" placeholder="e.g. Broken item, physical stock audit count"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeAdjustmentModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow">Apply Adjustment</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdjustmentModal(productId = null) {
        const modal = document.getElementById('adjustmentModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (productId) {
            document.getElementById('adj_product_id').value = productId;
        }
    }

    function closeAdjustmentModal() {
        const modal = document.getElementById('adjustmentModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
