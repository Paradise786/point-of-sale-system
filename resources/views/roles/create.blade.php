@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Role</h1>
            <p class="text-xs text-slate-500 mt-1">Define role name, general capabilities, and entity permission matrix.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Roles</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('roles.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <!-- Role Name -->
        <div>
            <label for="name" class="block text-sm font-bold text-slate-800 mb-1.5">
                Role Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                   placeholder="Developer">
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-bold text-slate-800 mb-1.5">
                Description
            </label>
            <input type="text" name="description" id="description" value="{{ old('description') }}"
                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                   placeholder="Developer">
        </div>

        <!-- Global Capability Checkboxes (from screenshot) -->
        <div class="space-y-3 pt-2">
            @foreach($systemOptions as $opt)
                @php
                    $perm = $permissionsBySlug->get($opt['slug']);
                @endphp
                @if($perm)
                    <label class="flex items-start gap-3 cursor-pointer group select-none">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                               {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}
                               class="global-perm-checkbox mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm text-slate-700 group-hover:text-slate-900">
                            {{ $opt['name'] }}
                        </span>
                    </label>
                @endif
            @endforeach
        </div>

        <!-- Permissions Table Section -->
        <div class="pt-4 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Permissions</h3>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleAllTable(true)" class="text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                        Select All
                    </button>
                    <span class="text-slate-300">•</span>
                    <button type="button" onclick="toggleAllTable(false)" class="text-xs font-semibold text-slate-500 hover:text-slate-700 underline">
                        Deselect All
                    </button>
                </div>
            </div>

            <!-- Table matching screenshot style -->
            <div class="border border-blue-200 rounded-lg overflow-hidden shadow-2xs">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-blue-200 text-slate-700">
                            <th class="py-3 px-4 font-bold text-slate-800 w-1/3">Entity</th>
                            <th class="py-2.5 px-3 text-center font-bold text-slate-800 w-1/6">
                                <div>View</div>
                                <button type="button" onclick="toggleColumn('view')" class="text-[11px] font-normal text-slate-500 hover:text-blue-600 underline cursor-pointer border-b border-dotted border-slate-400">
                                    Check All
                                </button>
                            </th>
                            <th class="py-2.5 px-3 text-center font-bold text-slate-800 w-1/6">
                                <div>Add</div>
                                <button type="button" onclick="toggleColumn('create')" class="text-[11px] font-normal text-slate-500 hover:text-blue-600 underline cursor-pointer border-b border-dotted border-slate-400">
                                    Check All
                                </button>
                            </th>
                            <th class="py-2.5 px-3 text-center font-bold text-slate-800 w-1/6">
                                <div>Edit</div>
                                <button type="button" onclick="toggleColumn('edit')" class="text-[11px] font-normal text-slate-500 hover:text-blue-600 underline cursor-pointer border-b border-dotted border-slate-400">
                                    Check All
                                </button>
                            </th>
                            <th class="py-2.5 px-3 text-center font-bold text-slate-800 w-1/6">
                                <div>Delete</div>
                                <button type="button" onclick="toggleColumn('delete')" class="text-[11px] font-normal text-slate-500 hover:text-blue-600 underline cursor-pointer border-b border-dotted border-slate-400">
                                    Check All
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($entities as $entityName => $entitySlug)
                            @php
                                $viewPerm = $permissionsBySlug->get($entitySlug . '.view');
                                $addPerm = $permissionsBySlug->get($entitySlug . '.create');
                                $editPerm = $permissionsBySlug->get($entitySlug . '.edit');
                                $delPerm = $permissionsBySlug->get($entitySlug . '.delete');
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition entity-row" data-entity="{{ $entitySlug }}">
                                <td class="py-2.5 px-4 font-medium text-slate-800">
                                    {{ $entityName }}
                                </td>
                                
                                <!-- View Checkbox -->
                                <td class="py-2.5 px-3 text-center">
                                    @if($viewPerm)
                                        <input type="checkbox" name="permissions[]" value="{{ $viewPerm->id }}"
                                               data-action="view" data-entity="{{ $entitySlug }}"
                                               {{ in_array($viewPerm->id, old('permissions', [])) ? 'checked' : '' }}
                                               class="matrix-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Add Checkbox -->
                                <td class="py-2.5 px-3 text-center">
                                    @if($addPerm)
                                        <input type="checkbox" name="permissions[]" value="{{ $addPerm->id }}"
                                               data-action="create" data-entity="{{ $entitySlug }}"
                                               {{ in_array($addPerm->id, old('permissions', [])) ? 'checked' : '' }}
                                               class="matrix-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Edit Checkbox -->
                                <td class="py-2.5 px-3 text-center">
                                    @if($editPerm)
                                        <input type="checkbox" name="permissions[]" value="{{ $editPerm->id }}"
                                               data-action="edit" data-entity="{{ $entitySlug }}"
                                               {{ in_array($editPerm->id, old('permissions', [])) ? 'checked' : '' }}
                                               class="matrix-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Delete Checkbox -->
                                <td class="py-2.5 px-3 text-center">
                                    @if($delPerm)
                                        <input type="checkbox" name="permissions[]" value="{{ $delPerm->id }}"
                                               data-action="delete" data-entity="{{ $entitySlug }}"
                                               {{ in_array($delPerm->id, old('permissions', [])) ? 'checked' : '' }}
                                               class="matrix-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
            <a href="{{ route('roles.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Save Role</span>
            </button>
        </div>
    </form>
</div>

<script>
    function toggleColumn(actionName) {
        const checkboxes = document.querySelectorAll(`.matrix-checkbox[data-action="${actionName}"]`);
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = anyUnchecked;
        });
    }

    function toggleAllTable(state) {
        document.querySelectorAll('.matrix-checkbox').forEach(cb => {
            cb.checked = state;
        });
        document.querySelectorAll('.global-perm-checkbox').forEach(cb => {
            cb.checked = state;
        });
    }
</script>
@endsection
