<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
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
    return redirect()->route('dashboard');
});

// Protected Application Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:dashboard.view');

    // Users & Roles Management
    Route::resource('users', UserController::class)->middleware('permission:users.view');
    Route::resource('roles', RoleController::class)->middleware('permission:roles.view');

    // POS (Point of Sale) Terminal
    Route::middleware('permission:pos.terminal')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Inventory: Products, Categories & Units
    Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode')->middleware('permission:products.view');
    Route::get('products/{product}/print-barcode', [ProductController::class, 'printBarcode'])->name('products.printBarcode')->middleware('permission:products.view');
    Route::resource('products', ProductController::class)->except(['show'])->middleware('permission:products.view');
    Route::resource('categories', CategoryController::class)->except(['show'])->middleware('permission:categories.view');
    Route::resource('units', UnitController::class)->except(['show'])->middleware('permission:units.view');

    // Stock Management & Movements
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    });

    // Customers & Vendors
    Route::resource('customers', CustomerController::class)->except(['show'])->middleware('permission:customers.view');
    Route::resource('vendors', VendorController::class)->except(['show'])->middleware('permission:vendors.view');

    // Sales: Orders, Invoices & Returns
    Route::resource('sale-orders', SaleOrderController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:sale_orders.view');
    Route::match(['get', 'post'], '/sale-orders/{saleOrder}/convert', [SaleOrderController::class, 'convertToInvoice'])->name('sale-orders.convert')->middleware('permission:sales.create');

    Route::get('/sales/fetch-from-order/{saleOrder}', [SaleController::class, 'fetchFromOrder'])->name('sales.fetchFromOrder')->middleware('permission:sales.create');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt')->middleware('permission:sales.view');
    Route::get('/sales/{sale}/print-preview', [SaleController::class, 'printPreview'])->name('sales.printPreview')->middleware('permission:sales.view');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:sales.view');
    Route::resource('sale-returns', SaleReturnController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:sales.return');

    // Purchases: Orders, Invoices & Returns
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:purchase_orders.view');
    Route::match(['get', 'post'], '/purchase-orders/{purchaseOrder}/convert', [PurchaseOrderController::class, 'convertToInvoice'])->name('purchase-orders.convert')->middleware('permission:purchases.create');

    Route::get('/purchases/fetch-from-order/{purchaseOrder}', [PurchaseController::class, 'fetchFromOrder'])->name('purchases.fetchFromOrder')->middleware('permission:purchases.create');
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt')->middleware('permission:purchases.view');
    Route::get('/purchases/{purchase}/print-preview', [PurchaseController::class, 'printPreview'])->name('purchases.printPreview')->middleware('permission:purchases.view');
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:purchases.view');
    Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show'])->middleware('permission:purchases.return');

    // Chart of Accounts: Ledgers (Khata)
    Route::get('/ledgers/customer', [LedgerController::class, 'customerLedger'])->name('ledgers.customer')->middleware('permission:ledgers.view');
    Route::get('/ledgers/vendor', [LedgerController::class, 'vendorLedger'])->name('ledgers.vendor')->middleware('permission:ledgers.view');

    // Analytics & Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
});
