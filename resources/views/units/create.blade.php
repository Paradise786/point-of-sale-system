@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Add New Unit</h2>
            <p class="text-xs text-slate-500 mt-0.5">Create a base unit or a converted unit (Box, Dozen, Carton, etc.)</p>
        </div>
        <a href="{{ route('units.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            Back to List
        </a>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('units.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Unit Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Box, Piece, Kilogram, Carton" required
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none transition">
                @error('name')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Short Code / Symbol *</label>
                <input type="text" name="short_code" value="{{ old('short_code') }}" placeholder="e.g. pc, box, ctn, kg, dz" required
                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white focus:outline-none transition">
                @error('short_code')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Conversion Settings</h4>
                <p class="text-xs text-slate-500">Leave Base Unit empty if this is a standalone root unit (like Piece or Kg).</p>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Base Unit (Parent)</label>
                    <select name="base_unit_id" id="base_unit_id" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">None (This is a Base Unit)</option>
                        @foreach ($baseUnits as $bu)
                            <option value="{{ $bu->id }}" {{ old('base_unit_id') == $bu->id ? 'selected' : '' }}>
                                {{ $bu->name }} ({{ $bu->short_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Operator</label>
                        <select name="operator" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="*" {{ old('operator', '*') == '*' ? 'selected' : '' }}>* (Multiply)</option>
                            <option value="/" {{ old('operator') == '/' ? 'selected' : '' }}>/ (Divide)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Conversion Factor</label>
                        <input type="number" step="any" name="conversion_factor" value="{{ old('conversion_factor', 1) }}" required
                               class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 font-mono">
                    Formula example: If 1 Box = 10 Pieces, select Base Unit: "Piece", Operator: "*", Factor: "10".
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('units.index') }}" class="px-4 py-2.5 text-slate-600 font-semibold text-xs hover:bg-slate-50 rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition">
                    Save Unit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
