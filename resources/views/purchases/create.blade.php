@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">New Purchase Invoice</h2>
            <p class="text-xs text-slate-500 mt-0.5">Record received vendor goods and create Purchase Invoice. Stock will be increased in base units upon saving.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Purchases
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" class="space-y-6">
        @csrf
        <input type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{ $selectedPo ? $selectedPo->id : old('purchase_order_id') }}">

        <!-- PO Converter Card (Optional) -->
        <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/5 rounded-2xl border border-emerald-200/80 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Convert from Purchase Order (PO)</h3>
                        <p class="text-xs text-slate-500">Auto-fill vendor, ordered items, packaging units &amp; agreed prices.</p>
                    </div>
                </div>
                <div class="sm:w-72">
                    <select id="po_selector" onchange="onPoSelect(this.value)" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-xs">
                        <option value="">-- Choose Pending PO (Optional) --</option>
                        @foreach ($pendingOrders as $po)
                            <option value="{{ $po->id }}" {{ ($selectedPo && $selectedPo->id == $po->id) || old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                {{ $po->po_number }} - {{ $po->vendor->name ?? 'Vendor' }} (Rs. {{ number_format($po->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Vendor & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-truck text-emerald-600"></i> Vendor &amp; Order Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Vendor Select -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="vendor_id" class="text-xs font-bold uppercase tracking-wider text-slate-600">Vendor / Supplier <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openQuickVendorModal()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> + Add New Vendor
                        </button>
                    </div>
                    <select name="vendor_id" id="vendor_id" required 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('vendor_id') border-rose-400 @enderror">
                        <option value="">Select Vendor</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ (old('vendor_id', $selectedPo?->vendor_id) == $vendor->id) ? 'selected' : '' }}>
                                {{ $vendor->name }} ({{ $vendor->phone ?? 'No phone' }})
                            </option>
                        @endforeach
                    </select>
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
                    <input type="text" name="note" id="note" value="{{ old('note', $selectedPo ? 'Converted from ' . $selectedPo->po_number : '') }}" placeholder="Invoice #, shipment ref..."
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
                    <p class="text-xs text-slate-400 mt-0.5">Select products, packaging units, and cost price.</p>
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
                            <th class="px-3 py-3" style="width: 35%;">Product</th>
                            <th class="px-3 py-3" style="width: 20%;">Unit / Packaging</th>
                            <th class="px-3 py-3 text-center" style="width: 15%;">Quantity</th>
                            <th class="px-3 py-3 text-right" style="width: 15%;">Unit Cost (Rs.)</th>
                            <th class="px-3 py-3 text-right" style="width: 10%;">Subtotal</th>
                            <th class="px-2 py-3 text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer" class="divide-y divide-slate-100">
                        <!-- Rows injected via JS -->
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold">
                        <tr>
                            <td colspan="4" class="px-4 py-3.5 text-right text-slate-600">Grand Total:</td>
                            <td class="px-3 py-3.5 text-right text-slate-900 text-base font-black" id="grandTotalDisplay">Rs. 0.00</td>
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
                    <p class="text-sm font-bold text-slate-100">Items will be credited to inventory in Base Units upon saving.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirm &amp; Increase Stock</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Product & PO list JSON for JavaScript -->
<script>
    const availableProducts = @json($products);
    const pendingOrdersList = @json($pendingOrders);
    const preselectedPo = @json($selectedPo);
    let rowIndex = 0;

    function getProductUnits(productId) {
        const product = availableProducts.find(p => p.id == productId);
        if (!product) return [];

        const list = [];
        if (product.unit) {
            list.push({
                unit_id: product.unit.id,
                name: product.unit.name,
                short_code: product.unit.short_code,
                conversion_rate: 1.0,
                purchase_price: parseFloat(product.purchase_price) || 0,
                is_base: true,
            });
        } else {
            list.push({
                unit_id: '',
                name: 'Base Unit',
                short_code: 'pc',
                conversion_rate: 1.0,
                purchase_price: parseFloat(product.purchase_price) || 0,
                is_base: true,
            });
        }

        if (product.secondary_units && product.secondary_units.length > 0) {
            product.secondary_units.forEach(su => {
                if (su.unit) {
                    const conv = parseFloat(su.conversion_rate) || 1.0;
                    const price = su.purchase_price !== null ? parseFloat(su.purchase_price) : (parseFloat(product.purchase_price) * conv);
                    list.push({
                        unit_id: su.unit.id,
                        name: su.unit.name,
                        short_code: su.unit.short_code,
                        conversion_rate: conv,
                        purchase_price: price,
                        is_base: false,
                    });
                }
            });
        }

        return list;
    }

    function addItemRow(data = null) {
        const container = document.getElementById('itemsContainer');
        const tr = document.createElement('tr');
        tr.id = `row_${rowIndex}`;
        tr.className = 'hover:bg-slate-50/50 transition';

        let productOptions = '<option value="">Select a Product</option>';
        availableProducts.forEach(p => {
            const selected = data && data.product_id == p.id ? 'selected' : '';
            const baseUnit = p.unit ? p.unit.short_code : '';
            productOptions += `<option value="${p.id}" ${selected}>${p.name} (Stock: ${p.quantity} ${baseUnit})</option>`;
        });

        tr.innerHTML = `
            <td class="p-3">
                <select name="items[${rowIndex}][product_id]" required onchange="onProductSelect(this, ${rowIndex})"
                        class="product-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    ${productOptions}
                </select>
            </td>
            <td class="p-3">
                <select name="items[${rowIndex}][unit_id]" onchange="onUnitSelect(this, ${rowIndex})"
                        class="unit-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    <option value="">Base Unit</option>
                </select>
                <input type="hidden" name="items[${rowIndex}][conversion_rate]" class="conversion-rate-input" value="1">
            </td>
            <td class="p-3">
                <input type="number" min="1" value="${data ? data.quantity : 1}" name="items[${rowIndex}][quantity]" required oninput="calculateSubtotal(${rowIndex})"
                       class="qty-input w-full px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5 text-center unit-hint-${rowIndex}"></p>
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" value="${data ? parseFloat(data.purchase_price).toFixed(2) : '0.00'}" name="items[${rowIndex}][purchase_price]" required oninput="calculateSubtotal(${rowIndex})"
                       class="price-input w-full px-3 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </td>
            <td class="p-3 text-right font-black text-slate-800 text-xs" id="subtotal_${rowIndex}">
                Rs. 0.00
            </td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeRow(${rowIndex})" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Remove Row">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);

        // Populate unit dropdown if product is pre-selected
        if (data && data.product_id) {
            updateUnitDropdown(rowIndex, data.product_id, data.unit_id);
        }

        const currIndex = rowIndex;
        rowIndex++;
        calculateSubtotal(currIndex);
    }

    function updateUnitDropdown(id, productId, selectedUnitId = null, autoPrice = true) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const product = availableProducts.find(p => p.id == productId);
        const targetUnitId = selectedUnitId || (product ? product.default_purchase_unit_id : null);

        const unitSelect = row.querySelector('.unit-select');
        const units = getProductUnits(productId);

        let html = '';
        units.forEach(u => {
            const isSelected = targetUnitId ? (targetUnitId == u.unit_id) : u.is_base;
            const label = u.is_base ? `${u.name} (${u.short_code}) [Base]` : `${u.name} (= ${u.conversion_rate} Base)`;
            html += `<option value="${u.unit_id}" data-rate="${u.conversion_rate}" data-price="${u.purchase_price}" data-code="${u.short_code}" ${isSelected ? 'selected' : ''}>${label}</option>`;
        });

        unitSelect.innerHTML = html;
        onUnitSelect(unitSelect, id, autoPrice);
    }

    function onProductSelect(selectElement, id) {
        const productId = selectElement.value;
        if (!productId) {
            document.getElementById(`row_${id}`).querySelector('.unit-select').innerHTML = '<option value="">Base Unit</option>';
            calculateSubtotal(id);
            return;
        }

        updateUnitDropdown(id, productId, null, true);
    }

    function onUnitSelect(unitSelect, id, autoPrice = true) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const product = availableProducts.find(p => p.id == row.querySelector('.product-select')?.value);
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rate = parseFloat(selectedOption?.getAttribute('data-rate') || 1.0);
        let unitPrice = parseFloat(selectedOption?.getAttribute('data-price'));
        if (isNaN(unitPrice) || unitPrice <= 0) {
            unitPrice = (parseFloat(product?.purchase_price) || 0) * rate;
        }

        row.querySelector('.conversion-rate-input').value = rate;

        if (autoPrice) {
            row.querySelector('.price-input').value = unitPrice.toFixed(2);
        }

        calculateSubtotal(id);
    }

    function calculateSubtotal(id) {
        const row = document.getElementById(`row_${id}`);
        if (!row) return;

        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const rateInput = row.querySelector('.conversion-rate-input');
        const subtotalCell = document.getElementById(`subtotal_${id}`);
        const hintEl = row.querySelector(`.unit-hint-${id}`);

        const qty = parseFloat(qtyInput?.value) || 0;
        const price = parseFloat(priceInput?.value) || 0;
        const rate = parseFloat(rateInput?.value) || 1.0;
        const subtotal = qty * price;

        if (subtotalCell) {
            subtotalCell.innerText = 'Rs. ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        if (hintEl) {
            if (rate > 1) {
                const baseQty = qty * rate;
                hintEl.innerText = `≈ ${baseQty.toLocaleString()} base units`;
            } else {
                hintEl.innerText = '';
            }
        }

        updateGrandTotal();
    }

    function removeRow(id) {
        const row = document.getElementById(`row_${id}`);
        if (row) row.remove();
        updateGrandTotal();
    }

    function updateGrandTotal() {
        let total = 0;
        const container = document.getElementById('itemsContainer');
        const rows = container.querySelectorAll('tr');

        rows.forEach(r => {
            const id = r.id.replace('row_', '');
            const qty = parseFloat(r.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(r.querySelector('.price-input')?.value) || 0;
            total += qty * price;
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function onPoSelect(poId) {
        if (!poId) {
            document.getElementById('purchase_order_id').value = '';
            return;
        }

        const po = pendingOrdersList.find(p => p.id == poId);
        if (!po) return;

        document.getElementById('purchase_order_id').value = po.id;
        document.getElementById('vendor_id').value = po.vendor_id;
        document.getElementById('note').value = `Converted from Purchase Order: ${po.po_number}`;

        // Clear existing items and inject PO items
        document.getElementById('itemsContainer').innerHTML = '';
        rowIndex = 0;

        if (po.items && po.items.length > 0) {
            po.items.forEach(item => {
                addItemRow({
                    product_id: item.product_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity,
                    purchase_price: item.unit_price,
                });
            });
        }
    }

    // Initialize with pre-selected PO or 1 empty row
    document.addEventListener('DOMContentLoaded', function() {
        if (preselectedPo && preselectedPo.items && preselectedPo.items.length > 0) {
            onPoSelect(preselectedPo.id);
        } else {
            addItemRow();
        }
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
