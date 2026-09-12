<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Root Redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->hasPermission('dashboard.view')) {
            return redirect()->route('dashboard');
        }
        if ($user->hasPermission('pos.access')) {
            return redirect()->route('pos.index');
        }
        if ($user->hasPermission('sales.view')) {
            return redirect()->route('sales.index');
        }
        if ($user->hasPermission('products.view')) {
            return redirect()->route('products.index');
        }
    }

    return redirect()->route('dashboard');
});

// Protected Application Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:dashboard.view');

    // Users Management
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.edit');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Roles Management
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');

    // Permissions Management
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:permissions.view');
    Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:permissions.create');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:permissions.create');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:permissions.edit');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:permissions.edit');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:permissions.delete');

    // POS (Point of Sale) Terminal
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index')->middleware('permission:pos.access,pos.terminal');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search')->middleware('permission:pos.access,pos.terminal');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')->middleware('permission:pos.checkout,pos.access,pos.terminal');

    // Inventory: Products
    Route::get('products', [ProductController::class, 'index'])->name('products.index')->middleware('permission:products.view');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:products.create');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:products.edit');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:products.delete');
    Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode')->middleware('permission:products.barcode,products.view');
    Route::get('products/{product}/print-barcode', [ProductController::class, 'printBarcode'])->name('products.printBarcode')->middleware('permission:products.barcode,products.view');

    // Inventory: Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:categories.view');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create')->middleware('permission:categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:categories.create');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit')->middleware('permission:categories.edit');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:categories.edit');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:categories.delete');

    // Inventory: Units
    Route::get('units', [UnitController::class, 'index'])->name('units.index')->middleware('permission:units.view');
    Route::get('units/create', [UnitController::class, 'create'])->name('units.create')->middleware('permission:units.create');
    Route::post('units', [UnitController::class, 'store'])->name('units.store')->middleware('permission:units.create');
    Route::get('units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit')->middleware('permission:units.edit');
    Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update')->middleware('permission:units.edit');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy')->middleware('permission:units.delete');

    // Stock Management & Movements
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index')->middleware('permission:stock.view');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust')->middleware('permission:stock.adjust');
    Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements')->middleware('permission:stock.movements,stock.view');

    // Customers & Vendors
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:customers.view');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create')->middleware('permission:customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:customers.create');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:customers.edit');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:customers.delete');

    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index')->middleware('permission:vendors.view');
    Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create')->middleware('permission:vendors.create');
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store')->middleware('permission:vendors.create');
    Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit')->middleware('permission:vendors.edit');
    Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update')->middleware('permission:vendors.edit');
    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy')->middleware('permission:vendors.delete');

    // Sales: Orders, Invoices & Returns
    Route::get('sale-orders', [SaleOrderController::class, 'index'])->name('sale-orders.index')->middleware('permission:sale_orders.view');
    Route::get('sale-orders/create', [SaleOrderController::class, 'create'])->name('sale-orders.create')->middleware('permission:sale_orders.create');
    Route::post('sale-orders', [SaleOrderController::class, 'store'])->name('sale-orders.store')->middleware('permission:sale_orders.create');
    Route::get('sale-orders/{saleOrder}', [SaleOrderController::class, 'show'])->name('sale-orders.show')->middleware('permission:sale_orders.view');
    Route::match(['get', 'post'], '/sale-orders/{saleOrder}/convert', [SaleOrderController::class, 'convertToInvoice'])->name('sale-orders.convert')->middleware('permission:sale_orders.convert,sales.create');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index')->middleware('permission:sales.view');
    Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create')->middleware('permission:sales.create');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store')->middleware('permission:sales.create');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show')->middleware('permission:sales.view');
    Route::get('/sales/fetch-from-order/{saleOrder}', [SaleController::class, 'fetchFromOrder'])->name('sales.fetchFromOrder')->middleware('permission:sales.create');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt')->middleware('permission:sales.view,sales.show');
    Route::get('/sales/{sale}/print-preview', [SaleController::class, 'printPreview'])->name('sales.printPreview')->middleware('permission:sales.view,sales.show');

    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index')->middleware('permission:sale_returns.view,sales.return');
    Route::get('sale-returns/create', [SaleReturnController::class, 'create'])->name('sale-returns.create')->middleware('permission:sale_returns.create,sales.return');
    Route::post('sale-returns', [SaleReturnController::class, 'store'])->name('sale-returns.store')->middleware('permission:sale_returns.create,sales.return');
    Route::get('sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show')->middleware('permission:sale_returns.show,sale_returns.view,sales.return');

    // Purchases: Orders, Invoices & Returns
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index')->middleware('permission:purchase_orders.view');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('permission:purchase_orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store')->middleware('permission:purchase_orders.create');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show')->middleware('permission:purchase_orders.view');
    Route::match(['get', 'post'], '/purchase-orders/{purchaseOrder}/convert', [PurchaseOrderController::class, 'convertToInvoice'])->name('purchase-orders.convert')->middleware('permission:purchase_orders.convert,purchases.create');

    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('permission:purchases.view');
    Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:purchases.create');
    Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:purchases.create');
    Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:purchases.view');
    Route::get('/purchases/fetch-from-order/{purchaseOrder}', [PurchaseController::class, 'fetchFromOrder'])->name('purchases.fetchFromOrder')->middleware('permission:purchases.create');
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt')->middleware('permission:purchases.view,purchases.show');
    Route::get('/purchases/{purchase}/print-preview', [PurchaseController::class, 'printPreview'])->name('purchases.printPreview')->middleware('permission:purchases.view,purchases.show');

    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index')->middleware('permission:purchase_returns.view,purchases.return');
    Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create')->middleware('permission:purchase_returns.create,purchases.return');
    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store')->middleware('permission:purchase_returns.create,purchases.return');
    Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show')->middleware('permission:purchase_returns.show,purchase_returns.view,purchases.return');

    // Chart of Accounts: Ledgers (Khata)
    Route::get('/ledgers/customer', [LedgerController::class, 'customerLedger'])->name('ledgers.customer')->middleware('permission:ledgers.customer,ledgers.view');
    Route::get('/ledgers/vendor', [LedgerController::class, 'vendorLedger'])->name('ledgers.vendor')->middleware('permission:ledgers.vendor,ledgers.view');

    // Analytics & Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
});
