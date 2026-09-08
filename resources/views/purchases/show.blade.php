@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between no-print">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Purchase Order Details</h2>
            <p class="text-xs text-slate-500 mt-0.5">Reference: <span class="font-mono font-bold text-slate-700">{{ $purchase->reference_no }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Back to Purchases
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print PO
            </button>
        </div>
    </div>

    <!-- Purchase Order Slip -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-8 space-y-6">
        <!-- Top Metadata -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-black">
                        <i class="fa-solid fa-store text-sm"></i>
                    </div>
                    <span class="text-xl font-black text-slate-800">Smart<span class="text-emerald-500">POS</span></span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Purchase & Inward Stock Document</p>
            </div>
            <div class="sm:text-right">
                <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-emerald-100 text-emerald-700">
                    {{ $purchase->status }}
                </span>
                <p class="text-xs text-slate-400 mt-2 font-mono">Date: {{ $purchase->purchase_date->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Vendor and Buyer Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl text-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Vendor / Supplier</p>
                <p class="font-bold text-slate-800">{{ $purchase->vendor->name ?? 'Unassigned' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $purchase->vendor->phone ?? '' }}</p>
                <p class="text-xs text-slate-500">{{ $purchase->vendor->email ?? '' }}</p>
                <p class="text-xs text-slate-500">{{ $purchase->vendor->address ?? '' }}</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Order Information</p>
                <p class="font-mono text-xs font-bold text-slate-700">PO Ref: {{ $purchase->reference_no }}</p>
                <p class="text-xs text-slate-500 mt-1">Created: {{ $purchase->created_at->format('d M Y, h:i A') }}</p>
                @if ($purchase->note)
                    <p class="text-xs text-slate-500 mt-1 italic">Note: {{ $purchase->note }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Product Name</th>
                        <th class="px-5 py-3">Barcode</th>
                        <th class="px-5 py-3 text-center">Quantity</th>
                        <th class="px-5 py-3 text-right">Cost Price</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->items as $index => $item)
                        <tr>
                            <td class="px-5 py-3 text-xs text-slate-400 font-mono">{{ $index + 1 }}</td>
                            <td class="px-5 py-3 font-bold text-slate-800">{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $item->product->barcode ?? '—' }}</td>
                            <td class="px-5 py-3 text-center font-bold text-slate-700">{{ $item->quantity }}</td>
                            <td class="px-5 py-3 text-right font-medium">Rs. {{ number_format($item->purchase_price, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800">Rs. {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="5" class="px-5 py-4 text-right font-bold text-slate-700">Total Purchase Amount:</td>
                        <td class="px-5 py-4 text-right text-lg font-black text-slate-900">
                            Rs. {{ number_format($purchase->total_amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
