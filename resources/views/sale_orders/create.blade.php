@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Create Customer Sale Order (SO)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Booking / Quotation for customer. Note: <strong>Stock will NOT change</strong> until you confirm and convert to a Sale Invoice.</p>
        </div>
        <a href="{{ route('sale-orders.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <form action="{{ route('sale-orders.store') }}" method="POST" id="soForm" class="space-y-6">
        @csrf

        <!-- Customer & General Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user text-emerald-600"></i> Customer Details
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Select -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="customer_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Customer</label>
                        <button type="button" onclick="openQuickCustomerModal()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Add New Customer
                        </button>
                    </div>
                    <select name="customer_id" id="customer_id" 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="">Walk-in Customer (General Guest)</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $customer->phone ?? 'No phone' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Order Notes / Terms</label>
                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="Quotation terms, delivery deadline..."
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Ordered Items
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select products, quantity and selling prices.</p>
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
                            <th class="px-3 py-3" style="width: 35%;">Product</th>
                            <th class="px-3 py-3" style="width: 20%;">Unit / Packaging</th>
                            <th class="px-3 py-3 text-center" style="width: 15%;">Quantity</th>
                            <th class="px-3 py-3 text-right" style="width: 15%;">Selling Price (Rs.)</th>
                            <th class="px-3 py-3 text-right" style="width: 10%;">Subtotal</th>
                            <th class="px-2 py-3 text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="itemsTableBody">
                        <!-- Dynamic item rows injected via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Total summary -->
            <div class="flex justify-end pt-2">
                <div class="w-72 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <span>Total Items:</span>
                        <span class="font-bold text-slate-800" id="totalItemsCount">0</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-bold border-t border-slate-200 pt-2">
                        <span class="text-slate-800">Grand Total:</span>
                        <span class="text-xl font-black text-emerald-600" id="grandTotalDisplay">Rs. 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('sale-orders.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save Sale Order</span>
            </button>
        </div>
    </form>
</div>

<script>
    let rowCount = 0;
    const productsData = @json($products);

    function getProductUnits(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) return [];

        const list = [];
        if (product.unit) {
            list.push({
                unit_id: product.unit.id,
                name: product.unit.name,
                short_code: product.unit.short_code,
                conversion_rate: 1.0,
                sale_price: parseFloat(product.selling_price) || 0,
                is_base: true,
            });
        } else {
            list.push({
                unit_id: '',
                name: 'Base Unit',
                short_code: 'pc',
                conversion_rate: 1.0,
                sale_price: parseFloat(product.selling_price) || 0,
                is_base: true,
            });
        }

        if (product.secondary_units && product.secondary_units.length > 0) {
            product.secondary_units.forEach(su => {
                if (su.unit) {
                    const conv = parseFloat(su.conversion_rate) || 1.0;
                    const price = su.sale_price !== null ? parseFloat(su.sale_price) : (parseFloat(product.selling_price) * conv);
                    list.push({
                        unit_id: su.unit.id,
                        name: su.unit.name,
                        short_code: su.unit.short_code,
                        conversion_rate: conv,
                        sale_price: price,
                        is_base: false,
                    });
                }
            });
        }

        return list;
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.id = `so_row_${rowCount}`;
        tr.className = 'item-row hover:bg-slate-50/50 transition';

        let productOptions = '<option value="">Choose product...</option>';
        productsData.forEach(p => {
            const baseUnit = p.unit ? p.unit.short_code : '';
            productOptions += `<option value="${p.id}">${p.name} (Available: ${p.quantity} ${baseUnit})</option>`;
        });

        tr.innerHTML = `
            <td class="p-3">
                <select name="items[${rowCount}][product_id]" required onchange="handleProductChange(this, ${rowCount})"
                        class="product-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    ${productOptions}
                </select>
            </td>
            <td class="p-3">
                <select name="items[${rowCount}][unit_id]" onchange="handleUnitChange(this, ${rowCount})"
                        class="unit-select w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                    <option value="">Base Unit</option>
                </select>
                <input type="hidden" name="items[${rowCount}][conversion_rate]" class="conversion-rate-input" value="1">
            </td>
            <td class="p-3">
                <input type="number" min="1" name="items[${rowCount}][quantity]" value="1" required oninput="calculateTotals()"
                       class="item-qty w-full px-3 py-2 text-xs font-bold text-center bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5 text-center unit-hint-${rowCount}"></p>
            </td>
            <td class="p-3">
                <input type="number" step="0.01" min="0" name="items[${rowCount}][unit_price]" value="0.00" required oninput="calculateTotals()"
                       class="item-price w-full px-3 py-2 text-xs font-bold text-right bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </td>
            <td class="p-3 text-right font-black text-slate-800 item-subtotal text-xs" id="subtotal_${rowCount}">
                Rs. 0.00
            </td>
            <td class="p-3 text-center">
                <button type="button" onclick="removeItemRow(this)" class="text-slate-300 hover:text-rose-500 transition p-1">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        const currIndex = rowCount;
        rowCount++;
        calculateTotals();
    }

    function removeItemRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('A sale order must contain at least one item.');
            return;
        }
        btn.closest('tr').remove();
        calculateTotals();
    }

    function handleProductChange(select, id) {
        const productId = select.value;
        const row = document.getElementById(`so_row_${id}`);
        if (!row) return;

        const unitSelect = row.querySelector('.unit-select');
        if (!productId) {
            unitSelect.innerHTML = '<option value="">Base Unit</option>';
            row.querySelector('.item-price').value = '0.00';
            calculateTotals();
            return;
        }

        const product = productsData.find(p => p.id == productId);
        const units = getProductUnits(productId);
        const targetUnitId = product ? product.default_sale_unit_id : null;

        let html = '';
        units.forEach(u => {
            const isSelected = targetUnitId ? (targetUnitId == u.unit_id) : u.is_base;
            const label = u.is_base ? `${u.name} (${u.short_code}) [Base]` : `${u.name} (= ${u.conversion_rate} Base)`;
            html += `<option value="${u.unit_id}" data-rate="${u.conversion_rate}" data-price="${u.sale_price}" ${isSelected ? 'selected' : ''}>${label}</option>`;
        });

        unitSelect.innerHTML = html;
        handleUnitChange(unitSelect, id);
    }

    function handleUnitChange(unitSelect, id) {
        const row = document.getElementById(`so_row_${id}`);
        if (!row) return;

        const product = productsData.find(p => p.id == row.querySelector('.product-select')?.value);
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        const rate = parseFloat(selectedOption?.getAttribute('data-rate') || 1.0);
        let unitPrice = parseFloat(selectedOption?.getAttribute('data-price'));
        if (isNaN(unitPrice) || unitPrice <= 0) {
            unitPrice = (parseFloat(product?.selling_price) || 0) * rate;
        }

        row.querySelector('.conversion-rate-input').value = rate;
        row.querySelector('.item-price').value = unitPrice.toFixed(2);

        calculateTotals();
    }

    function calculateTotals() {
        let grandTotal = 0;
        let totalItems = 0;

        document.querySelectorAll('.item-row').forEach((row, idx) => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const rate = parseFloat(row.querySelector('.conversion-rate-input')?.value || 1.0);
            const subtotal = qty * price;

            row.querySelector('.item-subtotal').innerText = 'Rs. ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            grandTotal += subtotal;
            totalItems += qty;

            const hintEl = row.querySelector('p[class*="unit-hint-"]');
            if (hintEl) {
                if (rate > 1) {
                    hintEl.innerText = `≈ ${(qty * rate).toLocaleString()} base units`;
                } else {
                    hintEl.innerText = '';
                }
            }
        });

        document.getElementById('totalItemsCount').innerText = totalItems;
        document.getElementById('grandTotalDisplay').innerText = 'Rs. ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.addEventListener('DOMContentLoaded', function() {
        addItemRow();
    });
</script>



<!-- Quick Add Customer Modal -->
<div id="quickCustomerModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-base text-slate-800">Add New Customer</h3>
                <p class="text-xs text-slate-500">Add customer on the fly without page reload</p>
            </div>
            <button type="button" onclick="closeQuickCustomerModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="quickCustomerForm" onsubmit="submitQuickCustomer(event)" class="space-y-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Customer Name *</label>
                <input type="text" id="qc_name" required placeholder="e.g. Ali Khan"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Phone Number</label>
                <input type="text" id="qc_phone" placeholder="e.g. 0300-1234567"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                <input type="email" id="qc_email" placeholder="customer@example.com"
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Address / City</label>
                <input type="text" id="qc_address" placeholder="Lahore, Karachi, etc."
                       class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeQuickCustomerModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" id="qc_btn" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow">Save &amp; Select Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openQuickCustomerModal() {
    const modal = document.getElementById('quickCustomerModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('qc_name').focus();
}
function closeQuickCustomerModal() {
    const modal = document.getElementById('quickCustomerModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('quickCustomerForm').reset();
}
function submitQuickCustomer(e) {
    e.preventDefault();
    const btn = document.getElementById('qc_btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    fetch('{{ route('customers.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            name: document.getElementById('qc_name').value,
            phone: document.getElementById('qc_phone').value,
            email: document.getElementById('qc_email').value,
            address: document.getElementById('qc_address').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('customer_id');
            const option = new Option(data.customer.name + ' (' + (data.customer.phone || 'No phone') + ')', data.customer.id, true, true);
            select.appendChild(option);
            select.value = data.customer.id;
            closeQuickCustomerModal();
        } else {
            alert(data.message || 'Could not save customer. Please try again.');
        }
    })
    .catch(() => alert('Server error. Please try again.'))
    .finally(() => { btn.disabled = false; btn.textContent = 'Save & Select Customer'; });
}
</script>
@endsection

