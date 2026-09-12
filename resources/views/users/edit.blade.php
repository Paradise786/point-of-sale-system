@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Edit User</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update details, role assignment, and access state for <strong>{{ $user->name }}</strong>.</p>
        </div>
        <a href="{{ route('users.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Users</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-user text-sm"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </div>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">New Password <span class="text-slate-400 font-normal lowercase">(leave blank to keep current password)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <input type="password" name="password" id="password" minlength="6"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                           placeholder="••••••••">
                </div>
            </div>

            <!-- Role Assignment -->
            <div>
                <label for="role_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">User Role <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <select name="role_id" id="role_id" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <option value="">Select a Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} {{ $role->slug === 'super-admin' ? '(Full Unrestricted Access)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Active Status -->
            <div class="pt-2 border-t border-slate-100">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    <div>
                        <span class="text-sm font-semibold text-slate-800">Account is Active</span>
                        <p class="text-xs text-slate-400">
                            @if($user->id === auth()->id())
                                You cannot deactivate your own logged-in account.
                            @else
                                Inactive accounts cannot log in to the system.
                            @endif
                        </p>
                    </div>
                </label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
