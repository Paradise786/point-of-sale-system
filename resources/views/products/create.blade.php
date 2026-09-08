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
    function generateBarcode() {
        const rand = Math.floor(100000000000 + Math.random() * 900000000000);
        document.getElementById('barcode').value = rand;
    }
</script>
@endsection
