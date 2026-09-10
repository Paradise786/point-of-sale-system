@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('purchase-returns.index') }}" class="hover:text-slate-600 transition">Purchase Returns</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-slate-600 font-medium">{{ $purchaseReturn->return_number }}</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800">Purchase Return Detail</h2>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" 
                    class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>Print Return Slip</span>
            </button>
            <a href="{{ route('purchase-returns.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center gap-2">
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
                        <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                        Return #{{ $purchaseReturn->return_number }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-emerald-100 text-emerald-800">
                        Goods Dispatched &amp; Deducted
                    </span>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Return Slip #</p>
                        <p class="font-mono font-bold text-slate-800 mt-0.5">{{ $purchaseReturn->return_number }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $purchaseReturn->return_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Vendor</p>
                        <p class="font-bold text-slate-800 mt-0.5">{{ $purchaseReturn->vendor->name ?? 'Vendor' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Original Purchase</p>
                        @if ($purchaseReturn->purchase)
                            <a href="{{ route('purchases.show', $purchaseReturn->purchase) }}" class="font-mono font-bold text-emerald-600 hover:underline mt-0.5 block">
                                {{ $purchaseReturn->purchase->reference_no }}
                            </a>
                        @else
                            <p class="text-xs text-slate-400 mt-0.5">Direct Vendor Return</p>
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
                                <th class="px-6 py-3.5 text-right">Cost Price</th>
                                <th class="px-6 py-3.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach ($purchaseReturn->items as $index => $item)
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
                                    <td class="px-6 py-4 text-right font-black text-slate-900">
                                        Rs. {{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-slate-50 font-bold">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-right text-slate-600 uppercase text-xs">Total Return Value:</td>
                                <td class="px-6 py-4 text-right font-black text-slate-900 text-base">
                                    Rs. {{ number_format($purchaseReturn->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Cards (Right) -->
        <div class="space-y-6">
            <!-- Vendor Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck text-emerald-600"></i> Vendor / Supplier
                </h4>
                <div class="space-y-2 text-sm">
                    <p class="font-bold text-slate-800 text-base">{{ $purchaseReturn->vendor->name }}</p>
                    @if ($purchaseReturn->vendor->phone)
                        <p class="text-xs text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-phone text-slate-400"></i>
                            {{ $purchaseReturn->vendor->phone }}
                        </p>
                    @endif
                    @if ($purchaseReturn->vendor->email)
                        <p class="text-xs text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-slate-400"></i>
                            {{ $purchaseReturn->vendor->email }}
                        </p>
                    @endif
                    <div class="pt-3 border-t border-slate-100 mt-2">
                        <a href="{{ route('ledgers.vendor') }}?vendor_id={{ $purchaseReturn->vendor->id }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-book"></i> View Vendor Khata / Ledger
                        </a>
                    </div>
                </div>
            </div>

            <!-- Note -->
            @if ($purchaseReturn->note)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Return Reason / Note</h4>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $purchaseReturn->note }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
