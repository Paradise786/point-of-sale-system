@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Add New Product</h2>
            <p class="text-xs text-slate-500 mt-0.5">Register a product with barcode and pricing.</p>
        </div>
        <a href="{{ route('products.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Product Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Dell Inspiron 15, Wireless Mouse, Basmati Rice 5kg" 
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('name') border-rose-400 bg-rose-50/20 @enderror">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Barcode -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="barcode" class="text-xs font-bold uppercase tracking-wider text-slate-600">Barcode / SKU <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="generateBarcode()" class="text-[11px] font-bold text-emerald-600 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Auto Generate
                        </button>
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" required placeholder="Scan or enter barcode" 
                               class="w-full pl-10 pr-4 py-2.5 font-mono text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('barcode') border-rose-400 bg-rose-50/20 @enderror">
                    </div>
                    @error('barcode')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Category <span class="text-rose-500">*</span></label>
                    <select name="category_id" id="category_id" required 
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('category_id') border-rose-400 @enderror">
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Base Unit -->
                <div>
                    <label for="unit_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Base Unit</label>
                    <select name="unit_id" id="unit_id"
                            class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('unit_id') border-rose-400 @enderror">
                        <option value="">Select Unit (Piece, Box, Kg...)</option>
                        @foreach ($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->short_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purchase Price -->
                <div>
                    <label for="purchase_price" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Purchase / Cost Price (Rs.) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rs.</span>
                        <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', '0.00') }}" required
                               class="w-full pl-12 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('purchase_price') border-rose-400 @enderror">
                    </div>
                    @error('purchase_price')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Selling Price -->
                <div>
                    <label for="selling_price" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Selling / Retail Price (Rs.) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rs.</span>
                        <input type="number" step="0.01" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', '0.00') }}" required
                               class="w-full pl-12 pr-4 py-2.5 text-sm font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('selling_price') border-rose-400 @enderror">
                    </div>
                    @error('selling_price')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Initial Quantity -->
                <div>
                    <label for="quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Initial Stock Quantity <span class="text-rose-500">*</span></label>
                    <input type="number" min="0" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('quantity') border-rose-400 @enderror">
                    @error('quantity')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alert Quantity -->
                <div>
                    <label for="alert_quantity" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Low Stock Alert Threshold <span class="text-rose-500">*</span></label>
                    <input type="number" min="0" name="alert_quantity" id="alert_quantity" value="{{ old('alert_quantity', 5) }}" required
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('alert_quantity') border-rose-400 @enderror">
                    <span class="text-[10px] text-slate-400">Warning triggers when stock is at or below this value.</span>
                    @error('alert_quantity')
                        <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description / Specifications (Optional)</label>
                    <textarea name="description" id="description" rows="3" placeholder="Optional notes, size, color, brand..."
                              class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Secondary Units Configuration Card -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-emerald-600"></i> Secondary Units &amp; Packaging (Optional)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Define alternative packaging (e.g., 1 Box = 12 Pieces, 1 Carton = 24 Pieces) with custom prices.</p>
                    </div>
                    <button type="button" onclick="addSecondaryUnitRow()" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-plus"></i> Add Secondary Unit
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl bg-slate-50/50">
                    <table class="w-full text-left text-xs" id="secondaryUnitsTable">
                        <thead class="bg-slate-100 text-[11px] font-bold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5" style="width: 35%;">Packaging Unit</th>
                                <th class="px-3 py-2.5" style="width: 30%;">Conversion Rate <span class="text-slate-400 font-normal">(= ? Base Units)</span></th>
                                <th class="px-3 py-2.5" style="width: 30%;">Calculated Prices (Base × Rate)</th>
                                <th class="px-2 py-2.5 text-center" style="width: 5%;"></th>
                            </tr>
                        </thead>
                        <tbody id="secondaryUnitsContainer" class="divide-y divide-slate-200/70">
                            <!-- Dynamic rows injected via JS -->
                        </tbody>
                    </table>
                    <div id="noSecondaryUnitsMsg" class="p-4 text-center text-xs text-slate-400">
                        No secondary units configured yet. Click <strong>"+ Add Secondary Unit"</strong> to add box, carton, or pack conversions.
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info text-emerald-600"></i>
                    <span><strong>Auto Pricing:</strong> Prices for packaging units are always dynamically computed as <code>Base Price × Conversion Rate</code>. If base price changes, secondary unit prices update automatically.</span>
                </p>

                <!-- Default Units Preference -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-emerald-50/60 rounded-xl border border-emerald-200/80 mt-4">
                    <div>
                        <label for="default_sale_unit_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-800 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Default Sale Unit
                        </label>
                        <select name="default_sale_unit_id" id="default_sale_unit_id" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Default: Use Base Unit</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}" {{ old('default_sale_unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->short_code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Automatically chosen in POS &amp; Sale Invoices so you don't have to select it every time.</p>
                    </div>

                    <div>
                        <label for="default_purchase_unit_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-800 mb-1.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i> Default Purchase Unit
                        </label>
                        <select name="default_purchase_unit_id" id="default_purchase_unit_id" class="w-full px-3 py-2 text-xs font-semibold bg-white border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Default: Use Base Unit</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}" {{ old('default_purchase_unit_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->short_code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Automatically chosen in Purchase Invoices with converted cost price.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Save Product</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const availableUnitsList = @json($units);
    let secondaryUnitIndex = 0;

    function generateBarcode() {
        const rand = Math.floor(100000000000 + Math.random() * 900000000000);
        document.getElementById('barcode').value = rand;
    }

    function updateAllUnitPricePreviews() {
        const baseSale = parseFloat(document.getElementById('selling_price')?.value) || 0;
        const baseBuy = parseFloat(document.getElementById('purchase_price')?.value) || 0;

        document.querySelectorAll('#secondaryUnitsContainer tr').forEach(row => {
            const rateInput = row.querySelector('.rate-input');
            const previewEl = row.querySelector('.price-preview');
            if (rateInput && previewEl) {
                const rate = parseFloat(rateInput.value) || 0;
                if (rate > 0) {
                    const sPrice = (baseSale * rate).toFixed(2);
                    const bPrice = (baseBuy * rate).toFixed(2);
                    previewEl.innerHTML = `<span class="text-emerald-700 font-bold">Sell: Rs. ${Number(sPrice).toLocaleString()}</span> <span class="text-slate-300 mx-1">|</span> <span class="text-slate-600">Buy: Rs. ${Number(bPrice).toLocaleString()}</span>`;
                } else {
                    previewEl.innerHTML = `<span class="text-slate-400 italic">Enter rate to preview</span>`;
                }
            }
        });
    }

    function addSecondaryUnitRow(data = null) {
        const container = document.getElementById('secondaryUnitsContainer');
        const emptyMsg = document.getElementById('noSecondaryUnitsMsg');
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const tr = document.createElement('tr');
        tr.id = `su_row_${secondaryUnitIndex}`;
        tr.className = 'hover:bg-white transition';

        const baseUnitId = document.getElementById('unit_id')?.value;
        let options = '<option value="">Select Unit</option>';
        availableUnitsList.forEach(u => {
            if (u.id != baseUnitId) {
                const selected = data && data.unit_id == u.id ? 'selected' : '';
                options += `<option value="${u.id}" ${selected}>${u.name} (${u.short_code})</option>`;
            }
        });

        const conversion = data ? data.conversion_rate : '';

        tr.innerHTML = `
            <td class="p-2.5">
                <select name="secondary_units[${secondaryUnitIndex}][unit_id]" required
                        class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    ${options}
                </select>
            </td>
            <td class="p-2.5">
                <input type="number" step="0.0001" min="0.0001" name="secondary_units[${secondaryUnitIndex}][conversion_rate]" value="${conversion}" required placeholder="e.g. 12"
                       oninput="updateAllUnitPricePreviews()"
                       class="rate-input w-full px-2.5 py-1.5 text-xs font-bold text-center bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </td>
            <td class="p-2.5">
                <div class="price-preview text-xs text-slate-700 bg-slate-100/80 px-2.5 py-1.5 rounded-lg border border-slate-200/80 flex items-center">
                    <span class="text-slate-400 italic">Enter rate to preview</span>
                </div>
            </td>
            <td class="p-2.5 text-center">
                <button type="button" onclick="removeSecondaryUnitRow(${secondaryUnitIndex})" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Remove">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        container.appendChild(tr);
        secondaryUnitIndex++;
        updateAllUnitPricePreviews();
    }

    function removeSecondaryUnitRow(index) {
        const row = document.getElementById(`su_row_${index}`);
        if (row) row.remove();

        const container = document.getElementById('secondaryUnitsContainer');
        if (container.querySelectorAll('tr').length === 0) {
            const emptyMsg = document.getElementById('noSecondaryUnitsMsg');
            if (emptyMsg) emptyMsg.classList.remove('hidden');
        }
    }
</script>
@endsection

