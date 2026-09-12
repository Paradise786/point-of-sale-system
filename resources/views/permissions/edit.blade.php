@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2.5">
                <i class="fa-solid fa-pen-to-square text-emerald-600"></i>
                <span>Edit Permission</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Modify permission details, code slug, or group category.</p>
        </div>
        <a href="{{ route('permissions.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Permissions</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('permissions.update', $permission) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Permission Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
        </div>

        <div>
            <label for="slug" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Permission Code / Slug <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $permission->slug) }}" required
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            <p class="text-[11px] text-slate-400 mt-1">Unique identifier used in code guards & route middleware (e.g. <code class="font-mono text-emerald-600">sales.create</code>).</p>
        </div>

        <div>
            <label for="group" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Module / Group Name <span class="text-rose-500">*</span>
            </label>
            <div class="space-y-2">
                <input type="text" name="group" id="group" value="{{ old('group', $permission->group) }}" list="group_suggestions" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                <datalist id="group_suggestions">
                    @foreach($existingGroups as $grp)
                        <option value="{{ $grp }}">{{ $grp }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>

        <div>
            <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Description / Purpose
            </label>
            <textarea name="description" id="description" rows="3"
                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('description', $permission->description) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="{{ route('permissions.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Update Permission</span>
            </button>
        </div>
    </form>
</div>
@endsection