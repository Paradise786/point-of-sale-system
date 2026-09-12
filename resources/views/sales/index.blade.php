@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Sales History & Invoices</h2>
            <p class="text-xs text-slate-500 mt-0.5">All completed sales transactions and generated invoices.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>+ New Sale Invoice</span>
            </a>
            <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Open POS Terminal</span>
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Revenue</p>
                <p class="text-xl font-black text-slate-800">Rs. {{ number_format($totalRevenue, 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Orders</p>
                <p class="text-xl font-black text-slate-800">{{ number_format($totalOrders) }} sales</p>
            </div>
        </div>
    </div>

    <!-- Filters Bar (ERP Style) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i> Apply Filter
            </h3>
            @if (!empty($search) || !empty($paymentMethod) || !empty($customerId) || !empty($dateFrom) || !empty($dateTo))
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset Filters
                </a>
            @endif
        </div>

        <form action="{{ route('sales.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search Keyword -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Invoice / Ref #</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search Invoice..." 
                           class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Customer Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Customer</label>
                <select name="customer_id" class="w-full px-3 py-2 text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Customers</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ (isset($customerId) && $customerId == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- From Date -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <!-- To Date -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <!-- Submit Filter Button -->
            <div>
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filter Invoices</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Invoice #</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Items</th>
                        <th class="px-5 py-3.5">Total Paid</th>
                        <th class="px-5 py-3.5">Payment</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <a href="{{ route('sales.show', $sale) }}" class="font-bold text-emerald-600 hover:underline font-mono text-xs">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-700">
                                {{ $sale->customer_display_name }}
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-semibold text-slate-600">
                                    {{ $sale->items->count() }} items
                                </span>
                            </td>
                            <td class="px-5 py-4 font-black text-slate-800">
                                Rs. {{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700' : ($sale->payment_method === 'card' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') }}">
                                    {{ str_replace('_', ' ', $sale->payment_method) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $sale->created_at->format('d M Y') }}
                                <span class="block text-[10px] text-slate-400">{{ $sale->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('sales.show', $sale) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="Invoice Detail">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="p-2 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition" title="Print Thermal Slip">
                                        <i class="fa-solid fa-print text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-receipt text-4xl text-slate-200 mb-3"></i>
                                    <p class="font-medium text-sm">No sales transactions found.</p>
                                    <a href="{{ route('pos.index') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                        Make your first sale on POS
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
