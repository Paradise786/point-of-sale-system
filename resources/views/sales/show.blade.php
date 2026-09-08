@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('sales.index') }}" class="hover:text-slate-600 transition">Sales History</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-slate-600 font-medium">{{ $sale->invoice_number }}</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800">Invoice Detail</h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" 
               class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Receipt</span>
            </a>
            <a href="{{ route('sales.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-semibold rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Detail (Left/Main) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Header Card -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-emerald-500"></i>
                        Invoice #{{ $sale->invoice_number }}
                    </h3>
                    <span class="px-3 py-1 text-xs font-bold uppercase rounded-full bg-emerald-100 text-emerald-700">
                        Completed
                    </span>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Invoice #</p>
                        <p class="font-mono font-bold text-slate-700 mt-0.5">{{ $sale->invoice_number }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                        <p class="font-semibold text-slate-700 mt-0.5">{{ $sale->created_at->format('d M Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Payment Method</p>
                        <span class="mt-0.5 inline-block px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700' : ($sale->payment_method === 'card' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') }}">
                            {{ str_replace('_', ' ', $sale->payment_method) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Total Items</p>
                        <p class="font-bold text-slate-700 mt-0.5">{{ $sale->items->count() }} items</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-basket-shopping text-emerald-500"></i>
                        Purchased Items
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">Product</th>
                                <th class="px-5 py-3 text-center">Qty</th>
                                <th class="px-5 py-3 text-right">Unit Price</th>
                                <th class="px-5 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($sale->items as $index => $item)
                                <tr>
                                    <td class="px-5 py-4 text-xs text-slate-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ $item->product->name ?? 'Product (deleted)' }}</p>
                                        @if ($item->product && $item->product->barcode)
                                            <p class="text-[10px] text-slate-400 font-mono">{{ $item->product->barcode }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="px-2.5 py-1 bg-slate-100 rounded-md font-bold text-slate-600">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">Rs. {{ number_format($item->price, 2) }}</td>
                                    <td class="px-5 py-4 text-right font-bold text-slate-800">Rs. {{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Customer + Payment Summary -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Customer Info -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-user text-emerald-500"></i>
                        Customer
                    </h3>
                </div>
                <div class="px-5 py-4 text-sm space-y-2 text-slate-600">
                    <p class="font-bold text-slate-800">{{ $sale->customer_display_name }}</p>
                    @if ($sale->customer)
                        @if ($sale->customer->phone)
                            <p class="flex items-center gap-2 text-xs">
                                <i class="fa-solid fa-phone w-4 text-slate-400"></i>
                                {{ $sale->customer->phone }}
                            </p>
                        @endif
                        @if ($sale->customer->email)
                            <p class="flex items-center gap-2 text-xs">
                                <i class="fa-solid fa-envelope w-4 text-slate-400"></i>
                                {{ $sale->customer->email }}
                            </p>
                        @endif
                        @if ($sale->customer->address)
                            <p class="flex items-center gap-2 text-xs">
                                <i class="fa-solid fa-location-dot w-4 text-slate-400"></i>
                                {{ $sale->customer->address }}
                            </p>
                        @endif
                    @else
                        <p class="text-xs text-slate-400 italic">Walk-in customer (no account)</p>
                    @endif
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-credit-card text-emerald-500"></i>
                        Payment Summary
                    </h3>
                </div>
                <div class="px-5 py-4 space-y-3 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-800">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Paid Amount</span>
                        <span class="font-semibold text-slate-800">Rs. {{ number_format($sale->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Change Given</span>
                        <span class="font-semibold text-slate-800">Rs. {{ number_format($sale->change_amount, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between font-bold">
                        <span class="text-base text-slate-800">Total</span>
                        <span class="text-xl text-emerald-600">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($sale->note)
                <!-- Note -->
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-sm text-amber-700">
                    <p class="font-bold mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-note-sticky"></i> Note
                    </p>
                    <p class="text-xs leading-relaxed">{{ $sale->note }}</p>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="flex flex-col gap-2">
                <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                   class="w-full text-center px-4 py-3 bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i>
                    Print Thermal Receipt
                </a>
                <a href="{{ route('pos.index') }}"
                   class="w-full text-center px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cart-shopping"></i>
                    New Sale on POS
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
