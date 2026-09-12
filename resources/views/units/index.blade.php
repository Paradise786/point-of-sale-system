@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Units & Conversions</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage measurement units (Piece, Box, Carton, Dozen, Kg) and their conversions.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('units.create'))
                <a href="{{ route('units.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Add Unit</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-4">
        <form action="{{ route('units.index') }}" method="GET" class="flex-1 flex items-center gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search unit by name or short code (e.g. pc, box, kg)..." 
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                Search
            </button>
            @if (!empty($search))
                <a href="{{ route('units.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Units Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Unit Name</th>
                        <th class="px-6 py-3.5">Short Code</th>
                        <th class="px-6 py-3.5">Type & Conversion</th>
                        <th class="px-6 py-3.5">Products Count</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($units as $unit)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-scale-balanced"></i>
                                </div>
                                <span>{{ $unit->name }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-600 font-bold">
                                {{ $unit->short_code }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if ($unit->baseUnit)
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                                        1 {{ $unit->short_code }} = {{ (float)$unit->conversion_factor }} {{ $unit->baseUnit->short_code }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Base Unit (Root)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-700">
                                    {{ $unit->products_count }} products
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()?->hasPermission('units.edit'))
                                        <a href="{{ route('units.edit', $unit) }}" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasPermission('units.delete'))
                                        <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this unit?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-scale-balanced text-4xl text-slate-200 mb-3"></i>
                                    <p class="font-medium text-sm">No units found.</p>
                                    <a href="{{ route('units.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                        Add your first unit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($units->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
