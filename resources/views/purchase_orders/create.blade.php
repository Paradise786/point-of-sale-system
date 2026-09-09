@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Create Purchase Order (PO)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Order items from vendor. Note: <strong>Stock will NOT change</strong> until you convert this order to a received invoice.</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm" class="space-y-6">
        @csrf

        <!-- Vendor & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-truck text-emerald-600"></i> Vendor & Order Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vendor Select -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="vendor_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Vendor / Supplier <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openQuickVendorModal()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Add New Vendor
                        </button>
                    </div>
                    <select name="vendor_id" id="vendor_id" required 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="">Select Vendor</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }} ({{ $vendor->phone ?? 'No phone' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Order Notes</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="Delivery instructions, estimated delivery date..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Ordered Products
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select products, quantity and agreed purchase price.</p>
                </div>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3" style="width: 45%;">Product</th>
                            <th class="px-4 py-3 text-center" style="width: 15%;">Quantity</th>
                            <th class="px-4 py-3 text-right" style="width: 20%;">Unit Price (Rs.)</th>
                            <th class="px-4 py-3 text-right" style="width: 15%;">Subtotal (Rs.)</th>
                            <th class="px-4 py-3 text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="itemsTableBody">
                        <!-- Default first row -->
                        <tr class="item-row">
                            <td class="p-3">
                                <select name="items[0][product_id]" required onchange="handleProductChange(this)" class="product-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                                    <option value="">Choose product...</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                            {{ $product->name }} (Stock: {{ $product->quantity }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-3">
                                <input type="number" min="1" name="items[0][quantity]" value="1" required oninput="calculateTotals()" class="item-qty w-full px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                            </td>
                            <td class="p-3">
                                <input type="number" step="0.01" min="0" name="items[0][unit_price]" value="0.00" required oninput="calculateTotals()" class="item-price w-full px-3 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                            </td>
                            <td class="p-3 text-right font-black text-slate-800 item-subtotal text-xs">
                                Rs. 0.00
                            </td>
                            <td class="p-3 text-center">
                                <button type="button" onclick="removeItemRow(this)" class="text-slate-300 hover:text-rose-500 transition p-1">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Total summary -->
            <div class="flex justify-end pt-2">
                <div class="w-72 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Total Items:</span>
                        <span class="font-bold text-slate-800" id="totalItemsCount">1</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-bold border-t border-slate-200 pt-2">
                        <span class="text-slate-800">Grand Total:</span>
                        <span class="text-xl font-black text-emerald-600" id="grandTotalDisplay">Rs. 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Save Purchase Order</span>
            </button>
        </div>
    </form>
</div>

<script>
    let rowCount = 1;
    const productsData = @json($products);

    function addItemRow() {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="p-3">
                <select name="items[${rowCount}][product_id]" required onchange="handleProductChange(this)" class="product-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">Choose product...</option>
                    ${productsData.map(p => `<option value="${p.id}" data-price="${p.purchase_price}">${p.name} (Stock: ${p.quantity})</option>`).join('')}
                </select>
            </td>
            <td class="p-3">
                <input type="number" min="1" name="items[${rowCount}][quantity]" value="1" required oninput="calculateTotals()" class="item-qty w-full px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" name="items[${rowCount}][unit_price]" value="0.00" required oninput="calculateTotals()" class="item-price w-full px-3 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </td>
            <td class="p-3 text-right font-black text-slate-800 item-subtotal text-xs">
                Rs. 0.00
            </td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeItemRow(this)" class="text-slate-300 hover:text-rose-500 transition p-1">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowCount++;
        calculateTotals();
    }

    function removeItemRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('A purchase order must contain at least one product.');
            return;
        }
        btn.closest('tr').remove();
        calculateTotals();
    }

    function handleProductChange(select) {
        const selected = select.options[select.selectedIndex];
        const price = selected.getAttribute('data-price') || 0;
        const row = select.closest('tr');
        row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let grandTotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = qty * price;

            row.querySelector('.item-subtotal').innerText = 'Rs. ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            grandTotal += subtotal;
            totalItems += qty;
        });

        document.getElementById('totalItemsCount').innerText = totalItems;
        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>

<!-- Quick Add Vendor Modal -->
<div id="quickVendorModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-800">Add New Vendor</h3>
                <p class="text-xs text-slate-500">Add supplier on the fly without page reload</p>
            </div>
            <button type="button" onclick="closeQuickVendorModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="quickVendorForm" onsubmit="submitQuickVendor(event)" class="space-y-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Vendor / Company Name *</label>
                <input type="text" id="qv_name" required placeholder="e.g. TechSupply Ltd."
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Phone Number</label>
                <input type="text" id="qv_phone" placeholder="e.g. 0300-1234567"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                <input type="email" id="qv_email" placeholder="vendor@example.com"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Address / City</label>
                <input type="text" id="qv_address" placeholder="Lahore, Karachi, etc."
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeQuickVendorModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" id="qv_btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow">Save &amp; Select Vendor</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuickVendorModal() {
    const modal = document.getElementById('quickVendorModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('qv_name').focus();
}
function closeQuickVendorModal() {
    const modal = document.getElementById('quickVendorModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('quickVendorForm').reset();
}
function submitQuickVendor(e) {
    e.preventDefault();
    const btn = document.getElementById('qv_btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    fetch('{{ route('vendors.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: document.getElementById('qv_name').value,
            phone: document.getElementById('qv_phone').value,
            email: document.getElementById('qv_email').value,
            address: document.getElementById('qv_address').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('vendor_id');
            const option = new Option(data.vendor.name + ' (' + (data.vendor.phone || 'No phone') + ')', data.vendor.id, true, true);
            select.appendChild(option);
            select.value = data.vendor.id;
            closeQuickVendorModal();
        } else {
            alert(data.message || 'Could not save vendor. Please try again.');
        }
    })
    .catch(() => alert('Server error. Please try again.'))
    .finally(() => { btn.disabled = false; btn.textContent = 'Save & Select Vendor'; });
}
</script>
@endsection

