@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('sale-returns.index') }}" class="hover:text-slate-600 transition">Sale Returns</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-slate-600 font-medium">{{ $saleReturn->return_number }}</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800">Return Slip Detail</h2>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" 
                    class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Print Slip</span>
            </button>
            <a href="{{ route('sale-returns.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Return Details Table (Left/Main) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-arrow-rotate-left text-amber-500"></i>
                        Return #{{ $saleReturn->return_number }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-amber-100 text-amber-800">
                        Processed &amp; Restored
                    </span>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Return Slip #</p>
                        <p class="font-mono font-bold text-slate-800 mt-0.5">{{ $saleReturn->return_number }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $saleReturn->return_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Settlement</p>
                        <span class="mt-0.5 inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $saleReturn->payment_status === 'refunded' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ str_replace('_', ' ', $saleReturn->payment_status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Original Invoice</p>
                        @if ($saleReturn->sale)
                            <a href="{{ route('sales.show', $saleReturn->sale) }}" class="font-mono font-bold text-emerald-600 hover:underline mt-0.5 block">
                                {{ $saleReturn->sale->invoice_number }}
                            </a>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">Direct Return</p>
                        @endif
                    </div>
                </div>

                <!-- Returned Items Table -->
                <div class="border-t border-slate-100 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">#</th>
                                <th class="px-6 py-3.5">Product</th>
                                <th class="px-6 py-3.5">Unit</th>
                                <th class="px-6 py-3.5 text-center">Returned Qty</th>
                                <th class="px-6 py-3.5 text-right">Unit Price</th>
                                <th class="px-6 py-3.5 text-right">Refund Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($saleReturn->items as $index => $item)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-xs text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-800">
                                        {{ $item->product->name ?? 'Product Deleted' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500 font-semibold">
                                        {{ $item->unit->name ?? 'Base Unit' }}
                                        @if ($item->conversion_rate > 1)
                                            <span class="block text-[10px] text-emerald-600">(= {{ $item->conversion_rate }} base)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-slate-800">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-700">
                                        Rs. {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-rose-600">
                                        Rs. {{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-slate-50 font-bold">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-right text-slate-600 uppercase text-xs">Total Refund:</td>
                                <td class="px-6 py-4 text-right font-black text-rose-600 text-base">
                                    Rs. {{ number_format($saleReturn->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Cards (Right) -->
        <div class="space-y-6">
            <!-- Customer Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-emerald-600"></i> Customer Details
                </h4>
                @if ($saleReturn->customer)
                    <div class="space-y-2 text-sm">
                        <p class="font-bold text-slate-800 text-base">{{ $saleReturn->customer->name }}</p>
                        @if ($saleReturn->customer->phone)
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-phone text-slate-400"></i>
                                {{ $saleReturn->customer->phone }}
                            </p>
                        @endif
                        @if ($saleReturn->customer->email)
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-envelope text-slate-400"></i>
                                {{ $saleReturn->customer->email }}
                            </p>
                        @endif
                        <div class="pt-3 border-t border-slate-100 mt-2">
                            <a href="{{ route('ledgers.customer') }}?customer_id={{ $saleReturn->customer->id }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-book"></i> View Customer Khata / Ledger
                            </a>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Walk-in Customer (Guest)</p>
                @endif
            </div>

            <!-- Settlement Note -->
            @if ($saleReturn->note)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Return Reason / Note</h4>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $saleReturn->note }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
