@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Edit Category</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update category information.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required 
                       class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition @error('name') border-rose-400 bg-rose-50/20 @enderror">
                @error('name')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description (Optional)</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('categories.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Update Category</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
