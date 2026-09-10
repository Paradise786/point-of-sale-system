@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Customer Ledger (کسٹمر کھاتہ)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Automated customer financial statements tracking sales, payments, returns, and outstanding receivables.</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($selectedCustomer)
                <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Statement</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Customer & Date Range Selector Filter Card (ERP Style) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-book-bookmark text-emerald-600"></i> Select Customer &amp; Date Range
            </h3>
            @if ($customerId || $dateFrom || $dateTo)
                <a href="{{ route('ledgers.customer') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Clear Selection
                </a>
            @endif
        </div>

        <form action="{{ route('ledgers.customer') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Customer Dropdown -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Customer <span class="text-rose-500">*</span></label>
                <select name="customer_id" required class="w-full px-3 py-2 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">-- Select Customer --</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone ?? 'No phone' }})
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

            <!-- Submit -->
            <div>
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-calculator text-xs"></i>
                    <span>Generate Ledger</span>
                </button>
            </div>
        </form>
    </div>

    @if ($selectedCustomer)
        <!-- Ledger Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-slate-400">Customer Name</p>
                <p class="text-lg font-black text-slate-800 mt-1">{{ $selectedCustomer->name }}</p>
                <p class="text-xs text-slate-500">{{ $selectedCustomer->phone ?? 'No phone' }}</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-slate-400">Total Billed (Debit)</p>
                <p class="text-xl font-black text-slate-900 mt-1">Rs. {{ number_format($totalDebit, 2) }}</p>
                <p class="text-[10px] text-slate-400">Total goods invoiced</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-emerald-600">Total Paid/Credited</p>
                <p class="text-xl font-black text-emerald-600 mt-1">Rs. {{ number_format($totalCredit, 2) }}</p>
                <p class="text-[10px] text-slate-400">Received &amp; returns</p>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <p class="text-[11px] font-bold uppercase {{ $closingBalance > 0 ? 'text-amber-600' : 'text-slate-500' }}">Current Balance</p>
                <p class="text-xl font-black {{ $closingBalance > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-1">
                    Rs. {{ number_format(abs($closingBalance), 2) }}
                    <span class="text-xs font-bold uppercase">{{ $closingBalance > 0 ? 'Receivable (Dr)' : ($closingBalance < 0 ? 'Advance (Cr)' : 'Cleared') }}</span>
                </p>
                <p class="text-[10px] text-slate-400">Net outstanding balance</p>
            </div>
        </div>

        <!-- Ledger Statement Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-emerald-600"></i> Account Activity Log
                </h3>
                <span class="text-xs text-slate-400 font-mono">{{ $ledgerEntries->count() }} transactions found</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5">Type</th>
                            <th class="px-5 py-3.5">Reference #</th>
                            <th class="px-5 py-3.5">Description</th>
                            <th class="px-5 py-3.5 text-right">Debit (Rs.)</th>
                            <th class="px-5 py-3.5 text-right">Credit (Rs.)</th>
                            <th class="px-5 py-3.5 text-right">Running Balance (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50/75 transition">
                                <td class="px-5 py-4 text-xs text-slate-600">
                                    {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $entry['type_badge'] }}">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs font-bold">
                                    @if ($entry['url'])
                                        <a href="{{ $entry['url'] }}" class="text-emerald-600 hover:underline">
                                            {{ $entry['reference'] }}
                                        </a>
                                    @else
                                        {{ $entry['reference'] }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-600">
                                    {{ $entry['description'] }}
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                    {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-semibold text-emerald-600">
                                    {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-black {{ $entry['running_balance'] > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                                    Rs. {{ number_format($entry['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open text-4xl text-slate-200 mb-2"></i>
                                    <p class="text-sm font-medium">No transactions recorded for this customer in selected period.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($ledgerEntries->count() > 0)
                        <tfoot class="border-t-2 border-slate-300 bg-slate-50/90 font-black text-xs">
                            <tr>
                                <td colspan="4" class="px-5 py-3 text-right uppercase text-slate-600">Totals:</td>
                                <td class="px-5 py-3 text-right text-slate-900">Rs. {{ number_format($totalDebit, 2) }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600">Rs. {{ number_format($totalCredit, 2) }}</td>
                                <td class="px-5 py-3 text-right {{ $closingBalance > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                                    Rs. {{ number_format($closingBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-16 text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto shadow-xs">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Select a Customer to View Ledger</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Choose a customer from the dropdown above to generate their complete running khata statement with automated debit/credit history.
            </p>
        </div>
    @endif
</div>
@endsection
