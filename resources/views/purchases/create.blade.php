@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">New Purchase Order</h2>
            <p class="text-xs text-slate-500 mt-0.5">Procure products from vendors. Stock quantities will be automatically increased.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Purchases
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" class="space-y-6">
        @csrf

        <!-- Vendor & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-truck text-emerald-600"></i> Vendor & Order Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Vendor Select -->
                    <div class="flex items-center justify-between mb-2">
                        <label for="vendor_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Vendor / Supplier <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openQuickVendorModal()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> + Add New Vendor
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <select name="vendor_id" id="vendor_id" required 
                                class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('vendor_id') border-rose-400 @enderror">
                            <option value="">Select Vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }} ({{ $vendor->phone ?? 'No phone' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('vendor_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purchase Date -->
                <div>
                    <label for="purchase_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Purchase Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('purchase_date') border-rose-400 @enderror">
                    @error('purchase_date')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Note -->
                <div>
                    <label for="note" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Order Note (Optional)</label>
                    <input type="text" name="note" id="note" value="{{ old('note') }}" placeholder="Invoice #, shipment ref..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Purchase Items Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Purchased Products
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select products and enter quantity and cost price.</p>
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
                            <th class="px-4 py-3" style="width: 18%;">Quantity</th>
                            <th class="px-4 py-3" style="width: 20%;">Purchase Price (Rs.)</th>
                            <th class="px-4 py-3" style="width: 17%;">Subtotal (Rs.)</th>
                            <th class="px-2 py-3 text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer" class="divide-y divide-slate-100">
                        <!-- Rows injected via JS -->
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3.5 text-right text-slate-600">Grand Total:</td>
                            <td class="px-4 py-3.5 text-slate-900 text-base font-black" id="grandTotalDisplay">Rs. 0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @error('items')
                <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-between p-6 bg-slate-900 text-white rounded-2xl shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-semibold">Stock Action</p>
                    <p class="text-sm font-bold text-slate-100">Items will be credited to inventory upon saving.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirm & Increase Stock</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Product list JSON for JavaScript -->
<script>
    const availableProducts = @json($products);
    let rowIndex = 0;

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;
        tr.className = 'hover:bg-slate-50/50 transition';

        let options = '<option value="">Select a Product</option>';
        availableProducts.forEach(p => {
            options += `<option value="${p.id}" data-price="${p.purchase_price}" data-stock="${p.quantity}">${p.name} (In stock: ${p.quantity})</option>`;
        });

        tr.innerHTML = `
            <td class="px-4 py-3">
                <select name="items[${rowIndex}][product_id]" required onchange="onProductSelect(this, ${rowIndex})"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    ${options}
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="number" min="1" value="1" name="items[${rowIndex}][quantity]" required oninput="calculateSubtotal(${rowIndex})"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </td>
            <td class="px-4 py-3">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold">Rs.</span>
                    <input type="number" step="0.01" min="0" value="0.00" name="items[${rowIndex}][purchase_price]" required oninput="calculateSubtotal(${rowIndex})"
                           class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </td>
            <td class="px-4 py-3 font-bold text-slate-800" id="subtotal_${rowIndex}">
                Rs. 0.00
            </td>
            <td class="px-2 py-3 text-center">
                <button type="button" onclick="removeRow(${rowIndex})" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Remove Row">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);
        rowIndex++;
        updateGrandTotal();
    }

    function onProductSelect(selectElement, id) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const defaultPrice = selectedOption.getAttribute('data-price') || 0;
        const priceInput = document.querySelector(`input[name="items[${id}][purchase_price]"]`);
        if (priceInput) {
            priceInput.value = parseFloat(defaultPrice).toFixed(2);
        }
        calculateSubtotal(id);
    }

    function calculateSubtotal(id) {
        const qtyInput = document.querySelector(`input[name="items[${id}][quantity]"]`);
        const priceInput = document.querySelector(`input[name="items[${id}][purchase_price]"]`);
        const subtotalCell = document.getElementById(`subtotal_${id}`);

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const subtotal = qty * price;

        if (subtotalCell) {
            subtotalCell.innerText = 'Rs. ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        updateGrandTotal();
    }

    function removeRow(id) {
        const row = document.getElementById(`row_${id}`);
        if (row) {
            row.remove();
        }
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        const container = document.getElementById('itemsContainer');
        const rows = container.querySelectorAll('tr');

        rows.forEach(r => {
            const id = r.id.replace('row_', '');
            const qty = parseFloat(document.querySelector(`input[name="items[${id}][quantity]"]`)?.value) || 0;
            const price = parseFloat(document.querySelector(`input[name="items[${id}][purchase_price]"]`)?.value) || 0;
            total += qty * price;
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Initialize with 1 empty row
    document.addEventListener('DOMContentLoaded', function() {
        addItemRow();
    });
</script>

<!-- Quick Add Vendor Modal -->
<div id="quickVendorModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4 animate-in fade-in zoom-in-95">
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
                <button type="submit" id="qv_btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow">Save & Select Vendor</button>
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
    }

    async function submitQuickVendor(e) {
        e.preventDefault();
        const btn = document.getElementById('qv_btn');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        const payload = {
            name: document.getElementById('qv_name').value,
            phone: document.getElementById('qv_phone').value,
            email: document.getElementById('qv_email').value,
            address: document.getElementById('qv_address').value,
        };

        try {
            const res = await fetch("{{ route('vendors.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (data.success && data.vendor) {
                const vendorSelect = document.getElementById('vendor_id');
                const opt = new Option(`${data.vendor.name} (${data.vendor.phone || 'No phone'})`, data.vendor.id, true, true);
                vendorSelect.add(opt);
                closeQuickVendorModal();
                document.getElementById('quickVendorForm').reset();
            } else {
                alert(data.message || 'Error saving vendor.');
            }
        } catch (err) {
            console.error(err);
            alert('Failed to save vendor. Please check all fields.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Save & Select Vendor';
        }
    }
</script>
@endsection
