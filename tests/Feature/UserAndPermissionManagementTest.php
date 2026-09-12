<?php

use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('super admin can access all management modules by default', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $this->actingAs($superAdmin)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get(route('users.index'))
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get(route('roles.index'))
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get(route('permissions.index'))
        ->assertOk();
});

test('super admin can create custom permission and it is auto-assigned to super admin', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $response = $this->actingAs($superAdmin)
        ->post(route('permissions.store'), [
            'name' => 'Manage Projects',
            'slug' => 'projects.manage',
            'group' => 'Project Management',
            'description' => 'Can create and deploy project releases',
        ]);

    $response->assertRedirect(route('permissions.index'));

    $permission = Permission::where('slug', 'projects.manage')->first();
    expect($permission)->not->toBeNull()
        ->and($permission->group)->toBe('Project Management');

    // Super Admin should automatically have this permission
    expect($superAdmin->hasPermission('projects.manage'))->toBeTrue();
});

test('super admin can create a custom role like Developer with specific permissions', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $allowedPerm = Permission::where('slug', 'products.view')->first();

    $response = $this->actingAs($superAdmin)
        ->post(route('roles.store'), [
            'name' => 'Developer',
            'description' => 'Software developer role',
            'permissions' => [$allowedPerm->id],
        ]);

    $response->assertRedirect(route('roles.index'));

    $developerRole = Role::where('name', 'Developer')->first();
    expect($developerRole)->not->toBeNull()
        ->and($developerRole->permissions->pluck('id')->toArray())->toContain($allowedPerm->id);
});

test('user with restricted role is forbidden from unpermitted modules but can access permitted ones', function () {
    $role = Role::create([
        'name' => 'Limited Staff',
        'guard_name' => 'web',
        'slug' => 'limited-staff',
    ]);

    $viewProductsPerm = Permission::where('slug', 'products.view')->orWhere('name', 'products.view')->first();
    $role->syncPermissions([$viewProductsPerm->id]);

    $user = User::create([
        'name' => 'Test Operator',
        'email' => 'operator@test.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $user->syncRoles([$role]);

    // Permitted module
    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk();

    // Forbidden modules (Users, Roles, Permissions, Purchases)
    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('permissions.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertForbidden();
});

test('logged in user cannot delete or deactivate own account', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $response = $this->actingAs($superAdmin)
        ->delete(route('users.destroy', $superAdmin));

    $response->assertSessionHas('error');
    expect(User::find($superAdmin->id))->not->toBeNull();
});

test('super admin can update role permissions and assign role to user', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $role = Role::create([
        'name' => 'Support Agent',
        'guard_name' => 'web',
        'slug' => 'support-agent',
    ]);

    $perm1 = Permission::where('name', 'customers.view')->first();
    $perm2 = Permission::where('name', 'customers.create')->first();

    $this->actingAs($superAdmin)
        ->put(route('roles.update', $role), [
            'name' => 'Support Specialist',
            'description' => 'Updated description',
            'permissions' => [(string) $perm1->id, (string) $perm2->id],
        ])
        ->assertRedirect(route('roles.index'));

    $role->refresh();
    expect($role->name)->toBe('Support Specialist')
        ->and($role->hasPermissionTo('customers.view'))->toBeTrue()
        ->and($role->hasPermissionTo('customers.create'))->toBeTrue();

    // Create user with this role
    $this->actingAs($superAdmin)
        ->post(route('users.store'), [
            'name' => 'Agent Smith',
            'email' => 'smith@test.com',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => 1,
        ])
        ->assertRedirect(route('users.index'));

    $smith = User::where('email', 'smith@test.com')->first();
    expect($smith)->not->toBeNull()
        ->and($smith->hasRole('Support Specialist'))->toBeTrue()
        ->and($smith->hasPermission('customers.view'))->toBeTrue()
        ->and($smith->hasPermission('users.view'))->toBeFalse();
});

test('cashier can access POS and view sales but is forbidden from creating products or viewing users', function () {
    $cashier = User::where('email', 'cashier@smartpos.com')->first();

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk();

    $this->actingAs($cashier)
        ->get(route('sales.index'))
        ->assertOk();

    // Forbidden actions
    $this->actingAs($cashier)
        ->get(route('products.create'))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($cashier)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('categories index renders and destroy route is defined', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();
    $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat']);

    $this->actingAs($superAdmin)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Test Cat');

    $this->actingAs($superAdmin)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    expect(Category::find($category->id))->toBeNull();
});

test('cashier role with dashboard.view can access dashboard, and root redirects smoothly', function () {
    $cashier = User::where('email', 'cashier@smartpos.com')->first();

    $this->actingAs($cashier)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($cashier)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
