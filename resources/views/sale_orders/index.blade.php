@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Sale Orders (Customer Bookings)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage customer orders/quotations. Stock remains untouched until order is confirmed into a Sale Invoice.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sale-orders.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create Sale Order</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('sale-orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by SO # or customer name..." 
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>⏳ Pending Order</option>
                    <option value="confirmed" {{ ($status ?? '') === 'confirmed' ? 'selected' : '' }}>✅ Invoiced & Fulfilled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">SO Number</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Items</th>
                        <th class="px-5 py-3.5">Total Amount</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Order Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-800">
                                {{ $order->so_number }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $order->customer->name ?? 'Walk-in Customer' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $order->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-4 font-black text-slate-800">
                                Rs. {{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($order->status === 'confirmed')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 uppercase">
                                        Confirmed & Invoiced
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 uppercase">
                                        Pending Confirmation
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $order->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('sale-orders.show', $order) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="View Details">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>

                                    @if ($order->status === 'pending')
                                        <form action="{{ route('sale-orders.convert', $order) }}" method="POST" onsubmit="return confirm('Confirm order and generate Sale Invoice? Stock will be reduced.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition flex items-center gap-1">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Confirm & Invoice</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-cart-flatbed text-4xl text-slate-200 mb-2"></i>
                                <p class="text-sm font-medium">No sale orders found.</p>
                                <a href="{{ route('sale-orders.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                    Create a Sale Order
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
