@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Vendors & Suppliers</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage suppliers from whom you purchase inventory stock.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->hasPermission('vendors.create'))
                <a href="{{ route('vendors.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-truck-medical text-xs"></i>
                    <span>Add Vendor</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('vendors.index') }}" method="GET" class="flex items-center gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search vendor by name, phone, or email..." 
                       class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
                Search
            </button>
            @if (!empty($search))
                <a href="{{ route('vendors.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Vendor Name</th>
                        <th class="px-6 py-3.5">Phone Number</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Address</th>
                        <th class="px-6 py-3.5">Purchase Orders</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-bold text-slate-800 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-truck"></i>
                                </div>
                                <span>{{ $vendor->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                {{ $vendor->phone ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $vendor->email ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs max-w-xs truncate">
                                {{ $vendor->address ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-700">
                                    {{ $vendor->purchases_count }} orders
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()?->hasPermission('vendors.edit'))
                                        <a href="{{ route('vendors.edit', $vendor) }}" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasPermission('vendors.delete'))
                                        <form action="{{ route('vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor?');" class="inline">
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-truck-ramp-box text-4xl text-slate-200 mb-3"></i>
                                    <p class="font-medium text-sm">No vendors registered yet.</p>
                                    <a href="{{ route('vendors.create') }}" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">
                                        Add your first vendor
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vendors->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
