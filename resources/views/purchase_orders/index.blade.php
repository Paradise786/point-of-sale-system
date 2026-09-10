@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Purchase Orders (Vendor Orders)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Place orders with vendors. Stock remains unchanged until orders are received and converted to invoice.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create Purchase Order</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <!-- Filters Bar (ERP Style) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i> Apply Filter
            </h3>
            @if (!empty($search) || !empty($status) || !empty($vendorId) || !empty($dateFrom) || !empty($dateTo))
                <a href="{{ route('purchase-orders.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset Filters
                </a>
            @endif
        </div>

        <form action="{{ route('purchase-orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">PO # or Vendor</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search PO #..." 
                           class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Vendor Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Vendor / Supplier</label>
                <select name="vendor_id" class="w-full px-3 py-2 text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Vendors</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" {{ (isset($vendorId) && $vendorId == $v->id) ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending Delivery</option>
                    <option value="converted" {{ ($status ?? '') === 'converted' ? 'selected' : '' }}>Converted to Invoice</option>
                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- From Date -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <!-- To Date & Submit -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition mb-2">
                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filter Orders</span>
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
                        <th class="px-5 py-3.5">PO Number</th>
                        <th class="px-5 py-3.5">Vendor</th>
                        <th class="px-5 py-3.5">Items</th>
                        <th class="px-5 py-3.5">Total Amount</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Created Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-800">
                                {{ $order->po_number }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $order->vendor->name ?? 'Unknown Vendor' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $order->items->count() }} item(s)
                            </td>
                            <td class="px-5 py-4 font-black text-slate-800">
                                Rs. {{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($order->isConverted())
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 uppercase inline-flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> Converted to Invoice
                                    </span>
                                @elseif ($order->status === 'cancelled')
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 uppercase">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 uppercase inline-flex items-center gap-1">
                                        <i class="fa-solid fa-clock"></i> Pending Delivery
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $order->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('purchase-orders.show', $order) }}" class="p-2 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition" title="View Order Details">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>

                                    @if ($order->isConverted())
                                        @if ($order->converted_purchase_id)
                                            <a href="{{ route('purchases.show', $order->converted_purchase_id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition flex items-center gap-1" title="View Purchase Invoice">
                                                <i class="fa-solid fa-receipt text-xs text-emerald-600"></i>
                                                <span>View Invoice</span>
                                            </a>
                                        @endif
                                    @elseif ($order->isPending())
                                        <button type="button" onclick="openConvertModal('{{ $order->id }}', '{{ $order->po_number }}', '{{ $order->vendor->name ?? 'Vendor' }}', '{{ number_format($order->total_amount, 2) }}')"
                                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-file-invoice"></i>
                                            <span>Convert into Purchase</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-list text-4xl text-slate-200 mb-2"></i>
                                <p class="text-sm font-medium">No purchase orders found.</p>
                                <a href="{{ route('purchase-orders.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                    Create a Purchase Order
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

<!-- Convert Confirmation Modal -->
<div id="convertPoModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4 animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-800">Convert Purchase Order into Purchase Invoice?</h3>
                    <p class="text-xs text-slate-500">Review items in Purchase Invoice and submit to update stock</p>
                </div>
            </div>
            <button type="button" onclick="closeConvertModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-500">Order Number:</span>
                <span class="font-mono font-bold text-slate-800" id="modal_po_number">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Vendor:</span>
                <span class="font-bold text-slate-800" id="modal_po_vendor">-</span>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2 font-bold">
                <span class="text-slate-700">Total Amount:</span>
                <span class="text-emerald-700" id="modal_po_total">-</span>
            </div>
        </div>

        <p class="text-xs text-slate-600">
            <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
            All order information will be transferred to the Purchase Invoice. Stock will only be increased after the invoice is submitted.
        </p>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeConvertModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer">Cancel</button>
            <button type="button" onclick="proceedToConvertPurchase()" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm cursor-pointer flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-right"></i>
                <span>Yes, Continue</span>
            </button>
        </div>
    </div>
</div>

<script>
    let activeConvertPoId = null;

    function openConvertModal(poId, poNumber, vendor, total) {
        activeConvertPoId = poId;
        document.getElementById('modal_po_number').textContent = poNumber;
        document.getElementById('modal_po_vendor').textContent = vendor;
        document.getElementById('modal_po_total').textContent = 'Rs. ' + total;

        const modal = document.getElementById('convertPoModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeConvertModal() {
        const modal = document.getElementById('convertPoModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        activeConvertPoId = null;
    }

    function proceedToConvertPurchase() {
        if (!activeConvertPoId) return;
        window.location.href = `{{ route('purchases.create') }}?purchase_order_id=${activeConvertPoId}`;
    }
</script>

@endsection
