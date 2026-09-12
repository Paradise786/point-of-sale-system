<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $groupFilter = $request->input('group');

        $query = Permission::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($groupFilter) {
            $query->where('group', $groupFilter);
        }

        $permissions = $query->orderBy('group')->orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(fn ($p) => $p->group ?: 'General');
        $allGroups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');

        return view('permissions.index', compact('groupedPermissions', 'allGroups', 'search', 'groupFilter', 'permissions'));
    }

    public function create(): View
    {
        $existingGroups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');

        return view('permissions.create', compact('existingGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,name', 'regex:/^[a-z0-9_\.\-]+$/i'],
            'group' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::lower($validated['slug']);

        $permission = Permission::create([
            'name' => $slug,
            'guard_name' => 'web',
            'slug' => $slug,
            'group' => $validated['group'],
            'description' => $validated['description'] ?? $validated['name'],
        ]);

        // Automatically assign newly created permission to Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->orWhere('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Permission "'.$validated['name'].'" created successfully and assigned to Super Admin by default.');
    }

    public function edit(Permission $permission): View
    {
        $existingGroups = Permission::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');

        return view('permissions.edit', compact('permission', 'existingGroups'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permission->id), 'regex:/^[a-z0-9_\.\-]+$/i'],
            'group' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = Str::lower($validated['slug']);

        $permission->update([
            'name' => $slug,
            'slug' => $slug,
            'group' => $validated['group'],
            'description' => $validated['description'] ?? $validated['name'],
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission "'.$validated['name'].'" updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
