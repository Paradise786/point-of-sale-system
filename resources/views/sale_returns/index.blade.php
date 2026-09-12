@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Sale Returns</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage returned customer merchandise, issue refunds, and adjust inventory.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('sale_returns.create') || auth()->user()?->hasPermission('sales.return'))
                <a href="{{ route('sale-returns.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create Sale Return</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Return Amount</p>
                <p class="text-xl font-black text-slate-800">Rs. {{ number_format($totalReturnAmount, 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Total Return Slips</p>
                <p class="text-xl font-black text-slate-800">{{ number_format($totalReturnCount) }} returns</p>
            </div>
        </div>
    </div>

    <!-- Filters Bar (ERP Style) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i> Apply Filter
            </h3>
            @if (!empty($search) || !empty($customerId) || !empty($dateFrom) || !empty($dateTo))
                <a href="{{ route('sale-returns.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset Filters
                </a>
            @endif
        </div>

        <form action="{{ route('sale-returns.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search Keyword -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Return / Invoice #</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search Return #..." 
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
                    <span>Filter Returns</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Return #</th>
                        <th class="px-5 py-3.5">Orig. Invoice</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Items Count</th>
                        <th class="px-5 py-3.5">Total Refund</th>
                        <th class="px-5 py-3.5">Settlement</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse ($returns as $ret)
                        <tr class="hover:bg-slate-50/75 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-800">
                                {{ $ret->return_number }}
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-700">
                                @if ($ret->sale)
                                    <a href="{{ route('sales.show', $ret->sale) }}" class="text-emerald-600 hover:underline font-mono">
                                        {{ $ret->sale->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Direct Return</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $ret->customer->name ?? 'Walk-in Customer' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $ret->return_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $ret->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-4 font-black text-rose-600">
                                Rs. {{ number_format($ret->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-md {{ $ret->payment_status === 'refunded' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }} uppercase">
                                    {{ str_replace('_', ' ', $ret->payment_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('sale-returns.show', $ret) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="View Details">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-arrow-rotate-left text-4xl text-slate-200 mb-2"></i>
                                <p class="text-sm font-medium">No sale returns recorded.</p>
                                <a href="{{ route('sale-returns.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline inline-block">
                                    Create a New Sale Return
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
