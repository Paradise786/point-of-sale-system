<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::all();
        $permissionsBySlug = $permissions->keyBy('slug');

        $systemOptions = $this->getSystemOptions();
        $entities = $this->getEntities();

        return view('roles.create', compact('permissions', 'permissionsBySlug', 'systemOptions', 'entities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            // Auto attach pos.checkout if pos.access is granted
            if ($permissions->contains('slug', 'pos.access')) {
                $checkoutPerm = Permission::where('slug', 'pos.checkout')->first();
                if ($checkoutPerm && ! $permissions->contains('id', $checkoutPerm->id)) {
                    $permissions->push($checkoutPerm);
                }
            }
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role "'.$role->name.'" created successfully.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::all();
        $permissionsBySlug = $permissions->keyBy('slug');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        $systemOptions = $this->getSystemOptions();
        $entities = $this->getEntities();

        return view('roles.edit', compact('role', 'permissions', 'permissionsBySlug', 'rolePermissions', 'systemOptions', 'entities'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Don't modify slug for default super-admin
        if ($role->slug !== 'super-admin' && $role->name !== 'Super Admin') {
            $role->update(['slug' => Str::slug($validated['name'])]);
            $permissions = ! empty($validated['permissions'])
                ? Permission::whereIn('id', $validated['permissions'])->get()
                : collect();

            // Auto attach pos.checkout if pos.access is granted
            if ($permissions->contains('slug', 'pos.access')) {
                $checkoutPerm = Permission::where('slug', 'pos.checkout')->first();
                if ($checkoutPerm && ! $permissions->contains('id', $checkoutPerm->id)) {
                    $permissions->push($checkoutPerm);
                }
            }

            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role "'.$role->name.'" updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->slug === 'super-admin' || $role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role with assigned users. Reassign users first.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * @return array<int, array{name: string, slug: string}>
     */
    private function getSystemOptions(): array
    {
        return [
            ['name' => 'Allow Dashboard Overview & Business Metrics', 'slug' => 'dashboard.view'],
            ['name' => 'Allow POS Screen / Counter Checkout', 'slug' => 'pos.access'],
            ['name' => 'Allow Financial Reports & Analytics', 'slug' => 'reports.view'],
            ['name' => 'Allow Customer Ledgers (Khata)', 'slug' => 'ledgers.customer'],
            ['name' => 'Allow Vendor Ledgers (Khata)', 'slug' => 'ledgers.vendor'],
            ['name' => 'Allow Stock Inventory Adjustments', 'slug' => 'stock.adjust'],
            ['name' => 'Allow Stock Movements Audit Log', 'slug' => 'stock.movements'],
            ['name' => 'Allow Print Product Barcode Labels', 'slug' => 'products.barcode'],
            ['name' => 'Allow Convert Sale Orders to Invoices', 'slug' => 'sale_orders.convert'],
            ['name' => 'Allow Convert Purchase Orders to Invoices', 'slug' => 'purchase_orders.convert'],
            ['name' => 'Allow access to time sheet (if checked user with this role will be able to see time sheet of other users)', 'slug' => 'timesheet.access'],
            ['name' => 'Allow access to People area (People list and Allocations report)', 'slug' => 'people.access'],
            ['name' => 'Has Effort', 'slug' => 'effort.access'],
            ['name' => 'Can change owner (edit permissions required)', 'slug' => 'owner.change'],
            ['name' => 'Can prioritize (edit permissions required)', 'slug' => 'priority.change'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getEntities(): array
    {
        return [
            'Project' => 'project',
            'Release' => 'release',
            'Iteration' => 'iteration',
            'User Story' => 'user_story',
            'Task' => 'task',
            'User' => 'users',
            'Time' => 'time',
            'Defect' => 'defect',
            'Feature' => 'feature',
            'Program' => 'program',
            'Build' => 'build',
            'Product' => 'products',
            'Category' => 'categories',
            'Unit' => 'units',
            'Sale Invoice' => 'sales',
            'Sale Order' => 'sale_orders',
            'Sale Return' => 'sale_returns',
            'Purchase Invoice' => 'purchases',
            'Purchase Order' => 'purchase_orders',
            'Purchase Return' => 'purchase_returns',
            'Stock' => 'stock',
            'Customer' => 'customers',
            'Vendor' => 'vendors',
            'Ledger' => 'ledgers',
            'Role' => 'roles',
            'Permission' => 'permissions',
        ];
    }
}
