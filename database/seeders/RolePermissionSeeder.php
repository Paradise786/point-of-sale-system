<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define All Granular Module Permissions
        $permissionsList = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'Dashboard', 'description' => 'Can view business metrics and dashboard overview'],

            // POS Terminal
            ['name' => 'Access POS Terminal', 'slug' => 'pos.access', 'group' => 'POS Terminal', 'description' => 'Can open and use the point of sale terminal'],
            ['name' => 'Process POS Checkout', 'slug' => 'pos.checkout', 'group' => 'POS Terminal', 'description' => 'Can complete sales transactions in POS'],

            // Sales & Invoices
            ['name' => 'View Sales History', 'slug' => 'sales.view', 'group' => 'Sales & Invoices', 'description' => 'Can view list of sale invoices'],
            ['name' => 'Create Sale Invoice', 'slug' => 'sales.create', 'group' => 'Sales & Invoices', 'description' => 'Can create new manual sale invoices'],
            ['name' => 'View Sale Invoice Details', 'slug' => 'sales.show', 'group' => 'Sales & Invoices', 'description' => 'Can view invoice details and thermal receipts'],

            // Sale Orders (Bookings)
            ['name' => 'View Sale Orders', 'slug' => 'sale_orders.view', 'group' => 'Sale Orders', 'description' => 'Can view customer bookings/orders'],
            ['name' => 'Create Sale Order', 'slug' => 'sale_orders.create', 'group' => 'Sale Orders', 'description' => 'Can create new customer booking orders'],
            ['name' => 'Convert Sale Order', 'slug' => 'sale_orders.convert', 'group' => 'Sale Orders', 'description' => 'Can convert sale orders into sale invoices'],

            // Sale Returns
            ['name' => 'View Sale Returns', 'slug' => 'sale_returns.view', 'group' => 'Sale Returns', 'description' => 'Can view customer return history'],
            ['name' => 'Create Sale Return', 'slug' => 'sale_returns.create', 'group' => 'Sale Returns', 'description' => 'Can accept and process customer returns'],
            ['name' => 'View Sale Return Details', 'slug' => 'sale_returns.show', 'group' => 'Sale Returns', 'description' => 'Can view sale return vouchers'],

            // Purchases & Invoices
            ['name' => 'View Purchases', 'slug' => 'purchases.view', 'group' => 'Purchases', 'description' => 'Can view received vendor invoices'],
            ['name' => 'Create Purchase Invoice', 'slug' => 'purchases.create', 'group' => 'Purchases', 'description' => 'Can create new purchase invoices and add stock'],
            ['name' => 'View Purchase Details', 'slug' => 'purchases.show', 'group' => 'Purchases', 'description' => 'Can view purchase invoice vouchers'],

            // Purchase Orders
            ['name' => 'View Purchase Orders', 'slug' => 'purchase_orders.view', 'group' => 'Purchase Orders', 'description' => 'Can view supplier purchase orders'],
            ['name' => 'Create Purchase Order', 'slug' => 'purchase_orders.create', 'group' => 'Purchase Orders', 'description' => 'Can create new supplier purchase orders'],
            ['name' => 'Convert Purchase Order', 'slug' => 'purchase_orders.convert', 'group' => 'Purchase Orders', 'description' => 'Can convert purchase orders into invoices'],

            // Purchase Returns
            ['name' => 'View Purchase Returns', 'slug' => 'purchase_returns.view', 'group' => 'Purchase Returns', 'description' => 'Can view vendor return records'],
            ['name' => 'Create Purchase Return', 'slug' => 'purchase_returns.create', 'group' => 'Purchase Returns', 'description' => 'Can return goods to suppliers'],
            ['name' => 'View Purchase Return Details', 'slug' => 'purchase_returns.show', 'group' => 'Purchase Returns', 'description' => 'Can view vendor return details'],

            // Products & Inventory
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'Products', 'description' => 'Can view product list and pricing'],
            ['name' => 'Create Product', 'slug' => 'products.create', 'group' => 'Products', 'description' => 'Can add new products with units & barcodes'],
            ['name' => 'Edit Product', 'slug' => 'products.edit', 'group' => 'Products', 'description' => 'Can edit product details and secondary units'],
            ['name' => 'Delete Product', 'slug' => 'products.delete', 'group' => 'Products', 'description' => 'Can delete products from system'],
            ['name' => 'Print Product Barcodes', 'slug' => 'products.barcode', 'group' => 'Products', 'description' => 'Can generate and print product barcode labels'],

            // Categories
            ['name' => 'View Categories', 'slug' => 'categories.view', 'group' => 'Categories', 'description' => 'Can view product categories'],
            ['name' => 'Create Category', 'slug' => 'categories.create', 'group' => 'Categories', 'description' => 'Can create new categories'],
            ['name' => 'Edit Category', 'slug' => 'categories.edit', 'group' => 'Categories', 'description' => 'Can edit categories'],
            ['name' => 'Delete Category', 'slug' => 'categories.delete', 'group' => 'Categories', 'description' => 'Can delete categories'],

            // Units & Conversions
            ['name' => 'View Units', 'slug' => 'units.view', 'group' => 'Units', 'description' => 'Can view base and secondary units'],
            ['name' => 'Create Unit', 'slug' => 'units.create', 'group' => 'Units', 'description' => 'Can create new measurement units'],
            ['name' => 'Edit Unit', 'slug' => 'units.edit', 'group' => 'Units', 'description' => 'Can edit units and conversion factors'],
            ['name' => 'Delete Unit', 'slug' => 'units.delete', 'group' => 'Units', 'description' => 'Can delete units'],

            // Stock Management
            ['name' => 'View Stock Levels', 'slug' => 'stock.view', 'group' => 'Stock Management', 'description' => 'Can view stock inventory table'],
            ['name' => 'Adjust Stock', 'slug' => 'stock.adjust', 'group' => 'Stock Management', 'description' => 'Can perform physical inventory stock adjustments'],
            ['name' => 'View Stock Movements', 'slug' => 'stock.movements', 'group' => 'Stock Management', 'description' => 'Can audit complete stock movement audit logs'],

            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'Customers', 'description' => 'Can view customer directory'],
            ['name' => 'Create Customer', 'slug' => 'customers.create', 'group' => 'Customers', 'description' => 'Can register new customers'],
            ['name' => 'Edit Customer', 'slug' => 'customers.edit', 'group' => 'Customers', 'description' => 'Can update customer info'],
            ['name' => 'Delete Customer', 'slug' => 'customers.delete', 'group' => 'Customers', 'description' => 'Can delete customers'],

            // Vendors
            ['name' => 'View Vendors', 'slug' => 'vendors.view', 'group' => 'Vendors', 'description' => 'Can view vendor/supplier directory'],
            ['name' => 'Create Vendor', 'slug' => 'vendors.create', 'group' => 'Vendors', 'description' => 'Can register new suppliers'],
            ['name' => 'Edit Vendor', 'slug' => 'vendors.edit', 'group' => 'Vendors', 'description' => 'Can update vendor info'],
            ['name' => 'Delete Vendor', 'slug' => 'vendors.delete', 'group' => 'Vendors', 'description' => 'Can delete vendors'],

            // Ledgers / Khata
            ['name' => 'View Customer Ledgers', 'slug' => 'ledgers.customer', 'group' => 'Ledgers (Khata)', 'description' => 'Can view customer accounts and outstanding balances'],
            ['name' => 'View Vendor Ledgers', 'slug' => 'ledgers.vendor', 'group' => 'Ledgers (Khata)', 'description' => 'Can view supplier accounts and payable balances'],

            // Analytics & Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'Reports', 'description' => 'Can view profit/loss and sales analytics'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'User Management', 'description' => 'Can view list of system users'],
            ['name' => 'Create User', 'slug' => 'users.create', 'group' => 'User Management', 'description' => 'Can add new system operators'],
            ['name' => 'Edit User', 'slug' => 'users.edit', 'group' => 'User Management', 'description' => 'Can edit user details and change roles'],
            ['name' => 'Delete User', 'slug' => 'users.delete', 'group' => 'User Management', 'description' => 'Can delete or deactivate user accounts'],

            // Roles Management
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'Roles & Matrix', 'description' => 'Can view roles and permissions matrix'],
            ['name' => 'Create Role', 'slug' => 'roles.create', 'group' => 'Roles & Matrix', 'description' => 'Can define new custom roles and matrix presets'],
            ['name' => 'Edit Role Permissions', 'slug' => 'roles.edit', 'group' => 'Roles & Matrix', 'description' => 'Can modify permissions assigned to roles'],
            ['name' => 'Delete Role', 'slug' => 'roles.delete', 'group' => 'Roles & Matrix', 'description' => 'Can remove custom roles'],

            // Permissions Manager
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'group' => 'Permissions Manager', 'description' => 'Can view full permissions list & modules'],
            ['name' => 'Create Permission', 'slug' => 'permissions.create', 'group' => 'Permissions Manager', 'description' => 'Can register new capability slugs & modules'],
            ['name' => 'Edit Permission', 'slug' => 'permissions.edit', 'group' => 'Permissions Manager', 'description' => 'Can modify permission metadata and groups'],
            ['name' => 'Delete Permission', 'slug' => 'permissions.delete', 'group' => 'Permissions Manager', 'description' => 'Can delete custom permissions from system'],

            // Global System Capabilities (from screenshot reference)
            ['name' => 'Allow access to time sheet', 'slug' => 'timesheet.access', 'group' => 'System Options', 'description' => 'If checked user with this role will be able to see time sheet of other users'],
            ['name' => 'Allow access to People area', 'slug' => 'people.access', 'group' => 'System Options', 'description' => 'People list and Allocations report'],
            ['name' => 'Has Effort', 'slug' => 'effort.access', 'group' => 'System Options', 'description' => 'Has effort estimation tracking'],
            ['name' => 'Can change owner', 'slug' => 'owner.change', 'group' => 'System Options', 'description' => 'Can change owner (edit permissions required)'],
            ['name' => 'Can prioritize', 'slug' => 'priority.change', 'group' => 'System Options', 'description' => 'Can prioritize (edit permissions required)'],

            // Entities (Project, Release, Iteration, User Story, Task, Defect, Feature, Program, Build, Time)
            ['name' => 'View Project', 'slug' => 'project.view', 'group' => 'Project', 'description' => 'View projects'],
            ['name' => 'Add Project', 'slug' => 'project.create', 'group' => 'Project', 'description' => 'Create projects'],
            ['name' => 'Edit Project', 'slug' => 'project.edit', 'group' => 'Project', 'description' => 'Edit projects'],
            ['name' => 'Delete Project', 'slug' => 'project.delete', 'group' => 'Project', 'description' => 'Delete projects'],

            ['name' => 'View Release', 'slug' => 'release.view', 'group' => 'Release', 'description' => 'View releases'],
            ['name' => 'Add Release', 'slug' => 'release.create', 'group' => 'Release', 'description' => 'Create releases'],
            ['name' => 'Edit Release', 'slug' => 'release.edit', 'group' => 'Release', 'description' => 'Edit releases'],
            ['name' => 'Delete Release', 'slug' => 'release.delete', 'group' => 'Release', 'description' => 'Delete releases'],

            ['name' => 'View Iteration', 'slug' => 'iteration.view', 'group' => 'Iteration', 'description' => 'View iterations'],
            ['name' => 'Add Iteration', 'slug' => 'iteration.create', 'group' => 'Iteration', 'description' => 'Create iterations'],
            ['name' => 'Edit Iteration', 'slug' => 'iteration.edit', 'group' => 'Iteration', 'description' => 'Edit iterations'],
            ['name' => 'Delete Iteration', 'slug' => 'iteration.delete', 'group' => 'Iteration', 'description' => 'Delete iterations'],

            ['name' => 'View User Story', 'slug' => 'user_story.view', 'group' => 'User Story', 'description' => 'View user stories'],
            ['name' => 'Add User Story', 'slug' => 'user_story.create', 'group' => 'User Story', 'description' => 'Create user stories'],
            ['name' => 'Edit User Story', 'slug' => 'user_story.edit', 'group' => 'User Story', 'description' => 'Edit user stories'],
            ['name' => 'Delete User Story', 'slug' => 'user_story.delete', 'group' => 'User Story', 'description' => 'Delete user stories'],

            ['name' => 'View Task', 'slug' => 'task.view', 'group' => 'Task', 'description' => 'View tasks'],
            ['name' => 'Add Task', 'slug' => 'task.create', 'group' => 'Task', 'description' => 'Create tasks'],
            ['name' => 'Edit Task', 'slug' => 'task.edit', 'group' => 'Task', 'description' => 'Edit tasks'],
            ['name' => 'Delete Task', 'slug' => 'task.delete', 'group' => 'Task', 'description' => 'Delete tasks'],

            ['name' => 'View Time', 'slug' => 'time.view', 'group' => 'Time', 'description' => 'View time entries'],
            ['name' => 'Add Time', 'slug' => 'time.create', 'group' => 'Time', 'description' => 'Log time entries'],
            ['name' => 'Edit Time', 'slug' => 'time.edit', 'group' => 'Time', 'description' => 'Edit time entries'],
            ['name' => 'Delete Time', 'slug' => 'time.delete', 'group' => 'Time', 'description' => 'Delete time entries'],

            ['name' => 'View Defect', 'slug' => 'defect.view', 'group' => 'Defect', 'description' => 'View defects/bugs'],
            ['name' => 'Add Defect', 'slug' => 'defect.create', 'group' => 'Defect', 'description' => 'Log defect'],
            ['name' => 'Edit Defect', 'slug' => 'defect.edit', 'group' => 'Defect', 'description' => 'Edit defect'],
            ['name' => 'Delete Defect', 'slug' => 'defect.delete', 'group' => 'Defect', 'description' => 'Delete defect'],

            ['name' => 'View Feature', 'slug' => 'feature.view', 'group' => 'Feature', 'description' => 'View features'],
            ['name' => 'Add Feature', 'slug' => 'feature.create', 'group' => 'Feature', 'description' => 'Create features'],
            ['name' => 'Edit Feature', 'slug' => 'feature.edit', 'group' => 'Feature', 'description' => 'Edit features'],
            ['name' => 'Delete Feature', 'slug' => 'feature.delete', 'group' => 'Feature', 'description' => 'Delete features'],

            ['name' => 'View Program', 'slug' => 'program.view', 'group' => 'Program', 'description' => 'View programs'],
            ['name' => 'Add Program', 'slug' => 'program.create', 'group' => 'Program', 'description' => 'Create programs'],
            ['name' => 'Edit Program', 'slug' => 'program.edit', 'group' => 'Program', 'description' => 'Edit programs'],
            ['name' => 'Delete Program', 'slug' => 'program.delete', 'group' => 'Program', 'description' => 'Delete programs'],

            ['name' => 'View Build', 'slug' => 'build.view', 'group' => 'Build', 'description' => 'View builds'],
            ['name' => 'Add Build', 'slug' => 'build.create', 'group' => 'Build', 'description' => 'Create builds'],
            ['name' => 'Edit Build', 'slug' => 'build.edit', 'group' => 'Build', 'description' => 'Edit builds'],
            ['name' => 'Delete Build', 'slug' => 'build.delete', 'group' => 'Build', 'description' => 'Delete builds'],
        ];

        $permissionModels = [];
        foreach ($permissionsList as $p) {
            $permissionModels[$p['slug']] = Permission::firstOrCreate(
                ['name' => $p['slug'], 'guard_name' => 'web'],
                [
                    'slug' => $p['slug'],
                    'group' => $p['group'],
                    'description' => $p['description'],
                ]
            );
        }

        // 2. Create Roles
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            [
                'slug' => 'super-admin',
                'description' => 'Full unrestricted system access with automatic bypass of all permission restrictions.',
                'is_system' => true,
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin / Manager', 'guard_name' => 'web'],
            [
                'slug' => 'admin',
                'description' => 'General business operations manager with access to inventory, sales, purchases, and reporting.',
                'is_system' => true,
            ]
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => 'Cashier', 'guard_name' => 'web'],
            [
                'slug' => 'cashier',
                'description' => 'Front-counter sales operator restricted to POS terminal, customer management, and sale receipts.',
                'is_system' => false,
            ]
        );

        $inventoryRole = Role::firstOrCreate(
            ['name' => 'Inventory Manager', 'guard_name' => 'web'],
            [
                'slug' => 'inventory-manager',
                'description' => 'Responsible for stocks, goods receiving purchases, supplier orders, and barcode generation.',
                'is_system' => false,
            ]
        );

        // 3. Assign Permissions
        // Super Admin gets all permissions
        $superAdminRole->syncPermissions(Permission::all());

        // Admin gets all operational modules
        $adminPermissions = Permission::whereNotIn('name', [
            'roles.create', 'roles.delete', 'users.delete',
        ])->get();
        $adminRole->syncPermissions($adminPermissions);

        // Cashier permissions
        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'pos.access',
            'pos.checkout',
            'sales.view',
            'sales.create',
            'sales.show',
            'sale_orders.view',
            'sale_orders.create',
            'sale_orders.convert',
            'sale_returns.view',
            'sale_returns.create',
            'sale_returns.show',
            'customers.view',
            'customers.create',
            'products.view',
        ])->get();
        $cashierRole->syncPermissions($cashierPermissions);

        // Inventory Manager permissions
        $inventoryPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view',
            'products.create',
            'products.edit',
            'products.barcode',
            'categories.view',
            'categories.create',
            'categories.edit',
            'units.view',
            'units.create',
            'units.edit',
            'stock.view',
            'stock.adjust',
            'stock.movements',
            'purchases.view',
            'purchases.create',
            'purchases.show',
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.convert',
            'purchase_returns.view',
            'purchase_returns.create',
            'purchase_returns.show',
            'vendors.view',
            'vendors.create',
            'vendors.edit',
        ])->get();
        $inventoryRole->syncPermissions($inventoryPermissions);

        // 4. Create Default Super Admin & Demo Cashier Users
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@smartpos.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $adminUser->syncRoles([$superAdminRole]);

        $cashierUser = User::updateOrCreate(
            ['email' => 'cashier@smartpos.com'],
            [
                'name' => 'Afeera Cashier',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $cashierUser->syncRoles([$cashierRole]);
    }
}
