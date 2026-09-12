@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Edit Role: {{ $role->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage details and configure active permissions for this role.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Roles</span>
        </a>
    </div>

    @if($role->slug === 'super-admin')
        <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-crown text-sm"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-amber-900">Super Admin Notice</h4>
                <p class="text-xs text-amber-700 mt-0.5">
                    The Super Admin role possesses permanent, universal permissions across all current and future modules. Explicit permission checkboxes are bypassed automatically in authorization gates.
                </p>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Details Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">Role Details</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Role Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                           {{ $role->slug === 'super-admin' ? 'readonly' : '' }}
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition {{ $role->slug === 'super-admin' ? 'cursor-not-allowed opacity-80' : '' }}"
                           placeholder="e.g. Cashier, Store Keeper">
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $role->description) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                           placeholder="Short summary of role scope and responsibilities">
                </div>
            </div>
        </div>

        @if($role->slug !== 'super-admin')
            <!-- Permissions Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Module Permissions</h3>
                        <p class="text-xs text-slate-500">Check the capabilities this role should be granted.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllPermissions(true)" class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                            Select All
                        </button>
                        <button type="button" onclick="toggleAllPermissions(false)" class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Permission Modules Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                                <span class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-2">
                                    <i class="fa-solid fa-folder-closed text-emerald-600"></i>
                                    {{ $module }}
                                </span>
                                <button type="button" onclick="toggleModulePermissions('{{ Str::slug($module) }}')" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-800">
                                    Toggle Group
                                </button>
                            </div>
                            <div class="p-4 space-y-3 flex-1">
                                @foreach($modulePermissions as $perm)
                                    <label class="flex items-start gap-3 cursor-pointer group">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               data-module="{{ Str::slug($module) }}"
                                               {{ in_array($perm->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                               class="permission-checkbox mt-0.5 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <div class="text-xs">
                                            <div class="font-bold text-slate-800 group-hover:text-emerald-600 transition">{{ $perm->name }}</div>
                                            <div class="text-slate-400 text-[11px]">{{ $perm->description ?? $perm->slug }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('roles.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition">
                Save Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleAllPermissions(checked) {
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    }

    function toggleModulePermissions(moduleSlug) {
        const checkboxes = document.querySelectorAll(`.permission-checkbox[data-module="${moduleSlug}"]`);
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = anyUnchecked;
        });
    }
</script>
@endpush
@endsection
