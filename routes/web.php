<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Inventory: Categories, Products & Units
Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('units', UnitController::class)->except(['show']);
Route::resource('products', ProductController::class)->except(['show']);

// Customers & Vendors
Route::resource('customers', CustomerController::class)->except(['show']);
Route::resource('vendors', VendorController::class)->except(['show']);

// Purchase Orders (Vendor Orders) & Purchase Invoices
Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
Route::post('/purchase-orders/{purchaseOrder}/convert', [PurchaseOrderController::class, 'convertToInvoice'])->name('purchase-orders.convert');
Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

// Sale Orders (Customer Bookings) & Sales Invoices
Route::resource('sale-orders', SaleOrderController::class)->only(['index', 'create', 'store', 'show']);
Route::post('/sale-orders/{saleOrder}/convert', [SaleOrderController::class, 'convertToInvoice'])->name('sale-orders.convert');

// Stock Management, Adjustments & Movements History
Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements');

// POS (Point of Sale) Terminal
Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

// Sales & Invoices
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

// Analytics & Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
