@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Roles & Permissions</h2>
            <p class="text-xs text-slate-500 mt-0.5">Define security roles and assign granular module permissions to control staff access.</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            @if(auth()->user()?->hasPermission('permissions.view'))
                <a href="{{ route('permissions.index') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-key text-xs text-emerald-600"></i>
                    <span>Permissions Manager</span>
                </a>
            @endif
            @if(auth()->user()?->hasPermission('users.view'))
                <a href="{{ route('users.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition flex items-center gap-2">
                    <i class="fa-solid fa-users text-xs text-slate-500"></i>
                    <span>Manage Users</span>
                </a>
            @endif
            @if(auth()->user()?->hasPermission('roles.create'))
                <a href="{{ route('roles.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-shield-plus text-xs"></i>
                    <span>Create New Role</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Super Admin Note -->
    <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid fa-crown text-sm"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-amber-900">Super Admin Bypass Rule</h4>
            <p class="text-xs text-amber-700 mt-0.5">
                By default, the <strong>Super Admin</strong> role has unrestricted access to all modules, pages, and actions in the application. Any newly created permission is automatically active for Super Admin without manual configuration.
            </p>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2.5">
                            @if($role->slug === 'super-admin')
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-crown text-base"></i>
                                </div>
                            @elseif($role->slug === 'cashier')
                                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-cash-register text-base"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-shield-halved text-base"></i>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $role->name }}</h3>
                                <span class="text-[11px] font-mono text-slate-400">{{ $role->slug }}</span>
                            </div>
                        </div>

                        @if($role->slug === 'super-admin')
                            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 rounded-full">System</span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-600 line-clamp-2 mb-4">
                        {{ $role->description ?? 'No description provided for this role.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-slate-400"></i>
                            <strong>{{ $role->users_count }}</strong> assigned {{ Str::plural('user', $role->users_count) }}
                        </span>
                        <span class="flex items-center gap-1.5 font-semibold {{ $role->slug === 'super-admin' ? 'text-amber-600' : 'text-slate-700' }}">
                            <i class="fa-solid fa-key text-slate-400"></i>
                            {{ $role->slug === 'super-admin' ? 'All Permissions' : $role->permissions_count . ' Permissions' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        @if(auth()->user()?->hasPermission('roles.edit'))
                            <a href="{{ route('roles.edit', $role) }}" class="px-3 py-1.5 text-xs font-bold text-indigo-600 hover:bg-indigo-50 rounded-lg transition flex items-center gap-1.5">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>{{ $role->slug === 'super-admin' ? 'View Details' : 'Edit Permissions' }}</span>
                            </a>
                        @endif

                        @if(auth()->user()?->hasPermission('roles.delete') && $role->slug !== 'super-admin')
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete role \'{{ $role->name }}\'?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
