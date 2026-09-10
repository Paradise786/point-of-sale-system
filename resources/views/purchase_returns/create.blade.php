@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">New Purchase Return</h2>
            <p class="text-xs text-slate-500 mt-0.5">Return goods to vendor. Returned items will be deducted from inventory stock in base units.</p>
        </div>
        <a href="{{ route('purchase-returns.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Returns
        </a>
    </div>

    <form action="{{ route('purchase-returns.store') }}" method="POST" id="purchaseReturnForm" class="space-y-6">
        @csrf

        <!-- Load from Purchase Invoice Card (Optional) -->
        <div class="bg-gradient-to-r from-teal-500/10 via-emerald-500/5 to-transparent rounded-2xl border border-emerald-200/80 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Return from Purchase Invoice</h3>
                        <p class="text-xs text-slate-500">Pick an existing invoice to auto-fill supplier &amp; purchased items.</p>
                    </div>
                </div>
                <div class="sm:w-72">
                    <select id="purchase_selector" onchange="onPurchaseSelect(this.value)" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-xs">
                        <option value="">-- Choose Invoice (Optional) --</option>
                        @foreach ($purchases as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPurchase && $selectedPurchase->id == $p->id) ? 'selected' : '' }}>
                                {{ $p->reference_no }} - {{ $p->vendor->name ?? 'Vendor' }} (Rs. {{ number_format($p->total_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Vendor & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-truck text-emerald-600"></i> Vendor &amp; Return Details
            </h3>

            <input type="hidden" name="purchase_id" id="purchase_id" value="{{ $selectedPurchase ? $selectedPurchase->id : '' }}">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Vendor Select -->
                <div>
                    <label for="vendor_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Vendor / Supplier <span class="text-rose-500">*</span></label>
                    <select name="vendor_id" id="vendor_id" required class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="">Select Vendor</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" {{ ($selectedPurchase && $selectedPurchase->vendor_id == $v->id) ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->phone ?? 'No phone' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Return Date -->
                <div>
                    <label for="return_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Return Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="return_date" id="return_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                <!-- Return Note -->
                <div>
                    <label for="note" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Reason / Note</label>
                    <input type="text" name="note" id="note" placeholder="Damaged items, supplier return..."
                           class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Returned Items Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Returned Products
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select products being sent back to supplier. Quantities will be deducted from warehouse stock.</p>
                </div>
                <button type="button" onclick="addItemRow()" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-4/12">Product</th>
                            <th class="p-3 w-3/12">Unit &amp; Conversion</th>
                            <th class="p-3 w-2/12 text-center">Return Qty</th>
                            <th class="p-3 w-2/12 text-right">Purchase Price (Rs.)</th>
                            <th class="p-3 w-2/12 text-right">Subtotal</th>
                            <th class="p-3 w-1/12 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer" class="divide-y divide-slate-100">
                        <!-- Dynamic Item Rows -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Total & Submit Bar -->
        <div class="bg-slate-900 text-white rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Total Return Value</span>
                <span class="text-3xl font-black text-emerald-400" id="grandTotalDisplay">Rs. 0.00</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-returns.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Confirm &amp; Deduct Stock</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    const availableProducts = @json($products);
    const purchasesList = @json($purchases);
    const preselectedPurchase = @json($selectedPurchase);
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
            productOptions += `<option value="${p.id}" ${selected}>${p.name} (Stock: ${p.quantity})</option>`;
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
                <input type="number" step="0.01" min="0" value="${data ? parseFloat(data.price).toFixed(2) : '0.00'}" name="items[${rowIndex}][unit_price]" required oninput="calculateSubtotal(${rowIndex})"
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

        if (data && data.product_id) {
            updateUnitDropdown(rowIndex, data.product_id, data.unit_id, false);
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
            const qty = parseFloat(r.querySelector('.qty-input')?.value) || 0;
            const price = parseFloat(r.querySelector('.price-input')?.value) || 0;
            total += qty * price;
        });

        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function onPurchaseSelect(purchaseId) {
        if (!purchaseId) {
            document.getElementById('purchase_id').value = '';
            return;
        }

        const purchase = purchasesList.find(p => p.id == purchaseId);
        if (!purchase) return;

        document.getElementById('purchase_id').value = purchase.id;
        if (purchase.vendor_id) {
            document.getElementById('vendor_id').value = purchase.vendor_id;
        }
        document.getElementById('note').value = `Return for Purchase: ${purchase.reference_no}`;

        document.getElementById('itemsContainer').innerHTML = '';
        rowIndex = 0;

        if (purchase.items && purchase.items.length > 0) {
            purchase.items.forEach(item => {
                addItemRow({
                    product_id: item.product_id,
                    unit_id: item.unit_id,
                    quantity: item.quantity,
                    price: item.unit_price,
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (preselectedPurchase && preselectedPurchase.items && preselectedPurchase.items.length > 0) {
            onPurchaseSelect(preselectedPurchase.id);
        } else {
            addItemRow();
        }
    });
</script>
@endsection
