@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Business Analytics & Reports</h2>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive audit reports for Sales, Purchases, Stock Values and Profit Margins.</p>
        </div>
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 no-print">
            <i class="fa-solid fa-print"></i>
            <span>Print Report</span>
        </button>
    </div>

    <!-- Date Range Filter Card -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm no-print">
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">From:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">To:</label>
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>

            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                Apply Date Filter
            </button>
        </form>
    </div>

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Sales</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <p class="text-xl font-black text-slate-800 mt-2">Rs. {{ number_format($totalSalesAmount, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $totalOrdersCount }} invoice(s) completed</p>
        </div>

        <!-- Purchases -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Purchases</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-truck"></i>
                </div>
            </div>
            <p class="text-xl font-black text-slate-800 mt-2">Rs. {{ number_format($totalPurchasesAmount, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $totalPurchasesCount }} purchase orders/invoices</p>
        </div>

        <!-- Stock Valuation -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Stock Value</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <p class="text-xl font-black text-slate-800 mt-2">Rs. {{ number_format($totalStockCost, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Retail: Rs. {{ number_format($totalStockRetail, 2) }}</p>
        </div>

        <!-- Expected Profit Margin -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Inventory Profit Potential</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <p class="text-xl font-black text-emerald-600 mt-2">Rs. {{ number_format($potentialProfit, 2) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Markup on current stock</p>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                <span class="flex items-center gap-2"><i class="fa-solid fa-money-bill-wave text-emerald-500"></i> Cash Sales</span>
                <span class="font-mono text-emerald-700 font-black">Rs. {{ number_format($cashSales, 2) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                <span class="flex items-center gap-2"><i class="fa-solid fa-credit-card text-blue-500"></i> Card Sales</span>
                <span class="font-mono text-blue-700 font-black">Rs. {{ number_format($cardSales, 2) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                <span class="flex items-center gap-2"><i class="fa-solid fa-building-columns text-purple-500"></i> Bank Transfer</span>
                <span class="font-mono text-purple-700 font-black">Rs. {{ number_format($bankSales, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
