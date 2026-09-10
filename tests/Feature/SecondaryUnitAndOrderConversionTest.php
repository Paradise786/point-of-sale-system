<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Unit;
use App\Models\Vendor;

test('it converts purchase order to purchase invoice and updates stock in base units', function () {
    $category = Category::create(['name' => 'Beverages', 'slug' => 'beverages']);
    $baseUnit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $boxUnit = Unit::create(['name' => 'Box (12 Pcs)', 'short_code' => 'box', 'base_unit_id' => $baseUnit->id, 'conversion_factor' => 12]);
    $vendor = Vendor::create(['name' => 'Beverage Dist', 'email' => 'dist@example.com']);

    $product = Product::create([
        'name' => 'Cola Can',
        'sku' => 'COLA-01',
        'barcode' => 'BC-COLA-01',
        'purchase_price' => 50,
        'selling_price' => 70,
        'quantity' => 10, // 10 pieces base
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $baseUnit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $boxUnit->id,
        'conversion_rate' => 12,
        'purchase_price' => 550,
        'sale_price' => 800,
    ]);

    // Create a PO of 2 Boxes (2 * 12 = 24 base units)
    $po = PurchaseOrder::create([
        'po_number' => 'PO-TEST-001',
        'vendor_id' => $vendor->id,
        'total_amount' => 1100,
        'status' => 'pending',
    ]);

    PurchaseOrderItem::create([
        'purchase_order_id' => $po->id,
        'product_id' => $product->id,
        'unit_id' => $boxUnit->id,
        'conversion_rate' => 12,
        'quantity' => 2,
        'unit_price' => 550,
        'subtotal' => 1100,
    ]);

    // Convert PO
    $response = $this->post(route('purchase-orders.convert', $po));
    $response->assertRedirect(route('purchases.index'));

    $po->refresh();
    $product->refresh();

    expect($po->status)->toBe('converted');
    // Initial was 10 + 24 = 34
    expect((int) $product->quantity)->toBe(34);
});

test('it converts sale order to sale invoice and deducts stock in base units', function () {
    $category = Category::create(['name' => 'Electronics', 'slug' => 'elec']);
    $baseUnit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $boxUnit = Unit::create(['name' => 'Box (10 Pcs)', 'short_code' => 'box', 'base_unit_id' => $baseUnit->id, 'conversion_factor' => 10]);
    $customer = Customer::create(['name' => 'Ali Store', 'email' => 'ali@example.com']);

    $product = Product::create([
        'name' => 'Flash Drive',
        'sku' => 'FD-01',
        'barcode' => 'BC-FD-01',
        'purchase_price' => 200,
        'selling_price' => 300,
        'quantity' => 50, // 50 pieces base
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $baseUnit->id,
    ]);

    ProductUnit::create([
        'product_id' => $product->id,
        'unit_id' => $boxUnit->id,
        'conversion_rate' => 10,
        'purchase_price' => 1800,
        'sale_price' => 2800,
    ]);

    // Create SO of 2 Boxes (2 * 10 = 20 base units)
    $so = SaleOrder::create([
        'so_number' => 'SO-TEST-001',
        'customer_id' => $customer->id,
        'total_amount' => 5600,
        'status' => 'pending',
    ]);

    SaleOrderItem::create([
        'sale_order_id' => $so->id,
        'product_id' => $product->id,
        'unit_id' => $boxUnit->id,
        'conversion_rate' => 10,
        'quantity' => 2,
        'unit_price' => 2800,
        'subtotal' => 5600,
    ]);

    // Convert SO
    $response = $this->post(route('sale-orders.convert', $so), [
        'payment_method' => 'cash',
    ]);
    $response->assertRedirect(route('sales.index'));

    $so->refresh();
    $product->refresh();

    expect($so->status)->toBe('converted');
    // Initial was 50 - 20 = 30
    expect((int) $product->quantity)->toBe(30);
});

test('it converts purchase order to invoice via purchase form submission and increases stock in base units', function () {
    $category = Category::create(['name' => 'Snacks', 'slug' => 'snacks']);
    $baseUnit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $boxUnit = Unit::create(['name' => 'Box (10 Pcs)', 'short_code' => 'box', 'base_unit_id' => $baseUnit->id, 'conversion_factor' => 10]);
    $vendor = Vendor::create(['name' => 'Snack Supplier', 'email' => 'supplier@example.com']);

    $product = Product::create([
        'name' => 'Potato Chips',
        'sku' => 'CHIP-01',
        'barcode' => 'BC-CHIP-01',
        'purchase_price' => 30,
        'selling_price' => 50,
        'quantity' => 15,
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $baseUnit->id,
    ]);

    $po = PurchaseOrder::create([
        'po_number' => 'PO-FORM-001',
        'vendor_id' => $vendor->id,
        'total_amount' => 600,
        'status' => 'pending',
    ]);

    $response = $this->post(route('purchases.store'), [
        'purchase_order_id' => $po->id,
        'vendor_id' => $vendor->id,
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $boxUnit->id,
                'conversion_rate' => 10,
                'quantity' => 2,
                'purchase_price' => 300,
            ],
        ],
    ]);

    $response->assertRedirect(route('purchases.index'));

    $po->refresh();
    $product->refresh();

    expect($po->status)->toBe('converted');
    // Initial 15 + (2 * 10) = 35 base units
    expect((int) $product->quantity)->toBe(35);
});

test('it converts sale order via POS checkout with online payment and deducts stock in base units', function () {
    $category = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets']);
    $baseUnit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $packUnit = Unit::create(['name' => 'Pack (5 Pcs)', 'short_code' => 'pack', 'base_unit_id' => $baseUnit->id, 'conversion_factor' => 5]);
    $customer = Customer::create(['name' => 'Mobile Shop', 'email' => 'shop@example.com']);

    $product = Product::create([
        'name' => 'Screen Protector',
        'sku' => 'PROT-01',
        'barcode' => 'BC-PROT-01',
        'purchase_price' => 50,
        'selling_price' => 100,
        'quantity' => 40,
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $baseUnit->id,
    ]);

    $so = SaleOrder::create([
        'so_number' => 'SO-POS-001',
        'customer_id' => $customer->id,
        'total_amount' => 900,
        'status' => 'pending',
    ]);

    $response = $this->postJson(route('pos.checkout'), [
        'customer_id' => $customer->id,
        'sale_order_id' => $so->id,
        'payment_method' => 'online',
        'paid_amount' => 900,
        'items' => [
            [
                'id' => $product->id,
                'unit_id' => $packUnit->id,
                'conversion_rate' => 5,
                'quantity' => 2,
                'price' => 450,
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $so->refresh();
    $product->refresh();

    expect($so->status)->toBe('converted');
    // Initial 40 - (2 * 5) = 30 base units
    expect((int) $product->quantity)->toBe(30);
});
