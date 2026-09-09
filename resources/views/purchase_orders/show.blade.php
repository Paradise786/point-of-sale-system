@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Purchase Order: {{ $purchaseOrder->po_number }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Created on {{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Back to Orders
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Print PO</span>
            </button>
            @if ($purchaseOrder->status === 'pending')
                <form action="{{ route('purchase-orders.convert', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Convert this PO to Purchase Invoice? Stock will increase automatically.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Receive Goods & Convert to Invoice</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- PO Details Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between pb-6 border-b border-slate-200 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Vendor Information</span>
                <h3 class="text-lg font-black text-slate-800 mt-1">{{ $purchaseOrder->vendor->name ?? 'Unknown' }}</h3>
                <p class="text-xs text-slate-500">{{ $purchaseOrder->vendor->phone ?? 'No phone' }}</p>
                <p class="text-xs text-slate-500">{{ $purchaseOrder->vendor->email ?? '' }}</p>
                <p class="text-xs text-slate-500">{{ $purchaseOrder->vendor->address ?? '' }}</p>
            </div>
            <div class="text-left sm:text-right space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Order Status</span>
                <div>
                    @if ($purchaseOrder->status === 'received')
                        <span class="px-3 py-1 text-xs font-black rounded-lg bg-emerald-100 text-emerald-800 uppercase">
                            Received & Invoiced
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-black rounded-lg bg-amber-100 text-amber-800 uppercase">
                            Pending Delivery
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 font-mono mt-2">PO Ref: {{ $purchaseOrder->po_number }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-right">Unit Price</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-4 py-3.5 font-bold text-slate-800">
                            <div>{{ $item->product->name ?? 'Deleted Product' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $item->product->barcode ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-700">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-4 py-3.5 text-right text-slate-600">
                            Rs. {{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-black text-slate-900">
                            Rs. {{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total -->
        <div class="flex justify-end pt-4 border-t border-slate-200">
            <div class="text-right space-y-1">
                <span class="text-xs text-slate-400 uppercase font-bold">Total Order Value</span>
                <p class="text-2xl font-black text-emerald-600">Rs. {{ number_format($purchaseOrder->total_amount, 2) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
