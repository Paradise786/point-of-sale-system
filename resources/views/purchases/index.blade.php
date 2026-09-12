@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Purchases & Stock In</h2>
            <p class="text-xs text-slate-500 mt-0.5">Procurement orders from vendors. Automatically increases inventory stock.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('purchases.create'))
                <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>New Purchase Invoice</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Purchases</p>
                <p class="text-xl font-black text-slate-800">Rs. {{ number_format($totalPurchasesAmount, 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Purchase Invoices</p>
                <p class="text-xl font-black text-slate-800">{{ number_format($totalPurchasesCount) }} invoices</p>
            </div>
        </div>
    </div>

    <!-- Filters Bar (ERP Style) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i> Apply Filter
            </h3>
            @if (!empty($search) || !empty($vendorId) || !empty($dateFrom) || !empty($dateTo))
                <a href="{{ route('purchases.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset Filters
                </a>
            @endif
        </div>

        <form action="{{ route('purchases.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search Keyword -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Reference #</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search Ref #..." 
                           class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Vendor Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Vendor / Supplier</label>
                <select name="vendor_id" class="w-full px-3 py-2 text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Vendors</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" {{ (isset($vendorId) && $vendorId == $v->id) ? 'selected' : '' }}>
                            {{ $v->name }}
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
                    <span>Filter Purchases</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Reference #</th>
                        <th class="px-6 py-3.5">Vendor</th>
                        <th class="px-6 py-3.5">Purchase Date</th>
                        <th class="px-6 py-3.5">Items Count</th>
                        <th class="px-6 py-3.5">Total Amount</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <a href="{{ route('purchases.show', $purchase) }}" class="text-emerald-600 hover:underline flex items-center gap-1.5 font-mono">
                                    <i class="fa-solid fa-file-invoice text-xs"></i>
                                    {{ $purchase->reference_no }}
                                </a>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">
                                {{ $purchase->vendor->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $purchase->purchase_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-700">
                                    {{ $purchase->items->count() }} line items
                                </span>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-800">
                                Rs. {{ number_format($purchase->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-md bg-emerald-100 text-emerald-700">
                                    {{ $purchase->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('purchases.show', $purchase) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-bag-shopping text-4xl text-slate-200 mb-3"></i>
                                    <p class="font-medium text-sm">No purchase orders recorded.</p>
                                    <a href="{{ route('purchases.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                        Create your first purchase order
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
