@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Welcome & Quick Action Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white p-6 md:p-8 rounded-2xl shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-store"></i> Point of Sale & Inventory System
            </div>
            <h2 class="text-2xl md:text-3xl font-black tracking-tight">SmartPOS Dashboard</h2>
            <p class="text-slate-300 text-sm mt-1">Real-time overview of sales, stock alerts, purchases, and customer activity.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('pos.index') }}" class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 flex items-center gap-2 transition duration-150 group">
                <i class="fa-solid fa-cart-plus text-base group-hover:scale-110 transition-transform"></i>
                <span>Open POS Terminal</span>
            </a>
            <a href="{{ route('purchases.create') }}" class="px-4 py-3 bg-slate-800/80 hover:bg-slate-700 text-slate-100 font-semibold rounded-xl border border-slate-700 flex items-center gap-2 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>New Purchase</span>
            </a>
            <a href="{{ route('products.create') }}" class="px-4 py-3 bg-slate-800/80 hover:bg-slate-700 text-slate-100 font-semibold rounded-xl border border-slate-700 flex items-center gap-2 transition">
                <i class="fa-solid fa-box text-xs"></i>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Sales -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Sales</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-sack-dollar text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-800">Rs. {{ number_format($totalSales, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up text-emerald-500"></i> Lifetime revenue
                </p>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Today's Sales</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-800">Rs. {{ number_format($todaySales, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    <span class="font-bold text-slate-700">{{ $todayOrders }}</span> orders placed today
                </p>
            </div>
        </div>

        <!-- Total Products -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Products</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-box text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-800">{{ number_format($totalProducts) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    Across <span class="font-bold text-slate-700">{{ $totalCategories }}</span> categories
                </p>
            </div>
        </div>

        <!-- Low Stock Warning -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Low Stock Alert</span>
                <div class="w-10 h-10 rounded-xl {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600 animate-pulse' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $lowStockCount }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    <a href="{{ route('stock.index', ['status' => 'low_stock']) }}" class="text-rose-600 hover:underline font-semibold">
                        View affected products &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Secondary Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Customers</p>
                <p class="text-lg font-black text-slate-800">{{ number_format($totalCustomers) }}</p>
            </div>
            <a href="{{ route('customers.index') }}" class="ml-auto text-xs text-indigo-600 font-semibold hover:underline">Manage</a>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Purchases (In)</p>
                <p class="text-lg font-black text-slate-800">Rs. {{ number_format($totalPurchases, 2) }}</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="ml-auto text-xs text-amber-600 font-semibold hover:underline">History</a>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Inventory Categories</p>
                <p class="text-lg font-black text-slate-800">{{ number_format($totalCategories) }}</p>
            </div>
            <a href="{{ route('categories.index') }}" class="ml-auto text-xs text-teal-600 font-semibold hover:underline">Manage</a>
        </div>
    </div>

    <!-- Data Tables Grid: Recent Sales & Low Stock Warning -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <h3 class="font-bold text-slate-800">Recent Sales</h3>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All &rarr;</a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Payment</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentSales as $sale)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-emerald-600 hover:underline">
                                        {{ $sale->invoice_number }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $sale->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $sale->customer_display_name }}
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-800">
                                    Rs. {{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-md uppercase {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $sale->payment_method }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="Print Slip">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-xs">
                                    No sales transactions recorded yet. <a href="{{ route('pos.index') }}" class="text-emerald-600 font-semibold underline">Make first sale</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Warning Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                    <h3 class="font-bold text-slate-800">Low Stock Warnings</h3>
                </div>
                <a href="{{ route('stock.index', ['status' => 'low_stock']) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Manage Stock &rarr;</a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Product</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">In Stock</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($lowStockProducts as $prod)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    {{ $prod->name }}
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $prod->barcode }}</span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs">
                                    {{ $prod->category->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-black {{ $prod->quantity <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ $prod->quantity }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">/ Alert: {{ $prod->alert_quantity }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($prod->quantity <= 0)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-700 uppercase">
                                            Out of Stock
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-700 uppercase">
                                            Low Stock
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('purchases.create') }}" class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg transition">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-emerald-600 text-xs font-medium">
                                    <i class="fa-solid fa-circle-check mr-1"></i> All products have sufficient stock levels!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
