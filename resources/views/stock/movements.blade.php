@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Stock Movement Audit History</h2>
            <p class="text-xs text-slate-500 mt-0.5">Chronological audit log of all stock increases, sales, and manual adjustments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('stock.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Stock Overview</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('stock.movements') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by reference (e.g. PI-001, INV-001, ADJ-001) or product name..."
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div class="flex items-center gap-2">
                <select name="type" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Movement Types</option>
                    <option value="purchase" {{ ($type ?? '') === 'purchase' ? 'selected' : '' }}>🟢 Purchase (Stock In)</option>
                    <option value="sale" {{ ($type ?? '') === 'sale' ? 'selected' : '' }}>🔴 Sale (Stock Out)</option>
                    <option value="adjustment_in" {{ ($type ?? '') === 'adjustment_in' ? 'selected' : '' }}>➕ Adjustment In (+)</option>
                    <option value="adjustment_out" {{ ($type ?? '') === 'adjustment_out' ? 'selected' : '' }}>➖ Adjustment Out (-)</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                    Filter
                </button>
                @if(!empty($search) || !empty($type))
                    <a href="{{ route('stock.movements') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5">Product</th>
                        <th class="px-5 py-3.5">Movement Type</th>
                        <th class="px-5 py-3.5 text-center">Change Qty</th>
                        <th class="px-5 py-3.5 text-center">Before & After</th>
                        <th class="px-5 py-3.5">Reference #</th>
                        <th class="px-5 py-3.5">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $m->created_at->format('d M Y') }}
                                <span class="block text-[10px] text-slate-400">{{ $m->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                <div>{{ $m->product->name ?? 'Unknown Product' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->product->barcode ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($m->type === 'purchase')
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 uppercase">
                                        Purchase (+ In)
                                    </span>
                                @elseif ($m->type === 'sale')
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 uppercase">
                                        Sale (- Out)
                                    </span>
                                @elseif ($m->type === 'adjustment_in')
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800 uppercase">
                                        Adjustment (+)
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 uppercase">
                                        Adjustment (-)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center font-black text-sm {{ in_array($m->type, ['purchase', 'adjustment_in']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ in_array($m->type, ['purchase', 'adjustment_in']) ? '+' : '-' }}{{ $m->quantity }}
                            </td>
                            <td class="px-5 py-3.5 text-center text-xs text-slate-600 whitespace-nowrap font-mono">
                                <span class="text-slate-400">{{ $m->before_quantity }}</span>
                                <i class="fa-solid fa-arrow-right text-[10px] text-slate-300 mx-1"></i>
                                <span class="font-bold text-slate-800">{{ $m->after_quantity }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-mono font-bold text-slate-700">
                                {{ $m->reference ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                                {{ $m->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clock-rotate-left text-4xl text-slate-200 mb-2"></i>
                                <p class="text-sm font-medium">No stock movement entries recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
