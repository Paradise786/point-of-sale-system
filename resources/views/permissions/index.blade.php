@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2.5">
                <i class="fa-solid fa-key text-emerald-600"></i>
                <span>Permissions Manager</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage system capabilities, module access rules, and custom permission slugs.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('roles.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-shield-halved text-slate-500"></i>
                <span>Roles List</span>
            </a>
            @if(auth()->user()?->hasPermission('permissions.create'))
                <a href="{{ route('permissions.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Permission</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Permissions</p>
                <h3 class="text-xl font-black text-slate-800">{{ $permissions->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Module Groups</p>
                <h3 class="text-xl font-black text-slate-800">{{ $allGroups->count() }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 flex items-center gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-shield-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Super Admin Bypass</p>
                <h3 class="text-xs font-bold text-emerald-600 mt-1">
                    <i class="fa-solid fa-circle-check"></i> 100% Granted By Default
                </h3>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('permissions.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search by name, slug (e.g. sales.create), or description..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <select name="group" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">All Module Groups</option>
                    @foreach($allGroups as $grp)
                        <option value="{{ $grp }}" {{ $groupFilter == $grp ? 'selected' : '' }}>{{ $grp }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                    Filter
                </button>
                @if($search || $groupFilter)
                    <a href="{{ route('permissions.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grouped Permissions List -->
    <div class="space-y-6">
        @forelse($groupedPermissions as $groupName => $groupPerms)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">{{ $groupName }}</h3>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-700">
                            {{ $groupPerms->count() }} permissions
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($groupPerms as $perm)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-800">{{ $perm->name }}</span>
                                    <code class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[11px] font-mono font-semibold border border-emerald-200">
                                        {{ $perm->slug }}
                                    </code>
                                </div>
                                @if($perm->description)
                                    <p class="text-xs text-slate-500">{{ $perm->description }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                @if(auth()->user()?->hasPermission('permissions.edit'))
                                    <a href="{{ route('permissions.edit', $perm) }}" 
                                       class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-emerald-600 bg-slate-100 hover:bg-emerald-50 rounded-lg transition"
                                       title="Edit Permission">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('permissions.delete'))
                                    <form action="{{ route('permissions.destroy', $perm) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this permission? It will be removed from all roles.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-rose-600 bg-slate-100 hover:bg-rose-50 rounded-lg transition" title="Delete Permission">
                                            <i class="fa-solid fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
                <i class="fa-solid fa-key text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-700">No Permissions Found</h3>
                <p class="text-xs text-slate-400 mt-1">Try adjusting your search query or create a new permission.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection