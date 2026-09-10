<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Vendor;

test('it creates product with default sale and purchase units', function () {
    $category = Category::create(['name' => 'Snacks', 'slug' => 'snacks-'.uniqid()]);
    $baseUnit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $boxUnit = Unit::create(['name' => 'Box', 'short_code' => 'box', 'conversion_factor' => 10]);

    $response = $this->post(route('products.store'), [
        'name' => 'Crispy Chips',
        'barcode' => 'BC-CHIPS-'.uniqid(),
        'category_id' => $category->id,
        'unit_id' => $baseUnit->id,
        'default_sale_unit_id' => $baseUnit->id,
        'default_purchase_unit_id' => $boxUnit->id,
        'purchase_price' => 30,
        'selling_price' => 50,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    $response->assertRedirect(route('products.index'));

    $product = Product::where('name', 'Crispy Chips')->first();
    expect($product)->not->toBeNull()
        ->and($product->default_sale_unit_id)->toBe($baseUnit->id)
        ->and($product->default_purchase_unit_id)->toBe($boxUnit->id);
});

test('it processes sale return and increments product stock', function () {
    $category = Category::create(['name' => 'General', 'slug' => 'gen-'.uniqid()]);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $customer = Customer::create(['name' => 'John Doe', 'phone' => '0300-1112233']);

    $product = Product::create([
        'name' => 'Returnable Item',
        'barcode' => 'BC-RET-'.uniqid(),
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'purchase_price' => 100,
        'selling_price' => 150,
        'quantity' => 20,
        'alert_quantity' => 5,
    ]);

    $response = $this->post(route('sale-returns.store'), [
        'customer_id' => $customer->id,
        'return_date' => date('Y-m-d'),
        'refund_amount' => 300,
        'payment_status' => 'refunded',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'conversion_rate' => 1,
                'quantity' => 2,
                'unit_price' => 150,
            ],
        ],
    ]);

    $return = SaleReturn::where('customer_id', $customer->id)->latest()->first();
    expect($return)->not->toBeNull()
        ->and((float) $return->total_amount)->toBe(300.0);

    $response->assertRedirect(route('sale-returns.show', $return));

    // Stock should increase from 20 to 22
    $product->refresh();
    expect($product->quantity)->toBe(22);

    // Stock movement should be recorded
    $movement = StockMovement::where('reference', $return->return_number)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('adjustment_in')
        ->and($movement->quantity)->toBe(2);
});

test('it processes purchase return and decrements product stock', function () {
    $category = Category::create(['name' => 'Hardware', 'slug' => 'hw-'.uniqid()]);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $vendor = Vendor::create(['name' => 'Hardware Supplier', 'phone' => '0311-2223344']);

    $product = Product::create([
        'name' => 'Hammer',
        'barcode' => 'BC-HAMMER-'.uniqid(),
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'purchase_price' => 200,
        'selling_price' => 300,
        'quantity' => 50,
        'alert_quantity' => 5,
    ]);

    $response = $this->post(route('purchase-returns.store'), [
        'vendor_id' => $vendor->id,
        'return_date' => date('Y-m-d'),
        'refund_amount' => 1000,
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'conversion_rate' => 1,
                'quantity' => 5,
                'unit_price' => 200,
            ],
        ],
    ]);

    $return = PurchaseReturn::where('vendor_id', $vendor->id)->latest()->first();
    expect($return)->not->toBeNull()
        ->and((float) $return->total_amount)->toBe(1000.0);

    $response->assertRedirect(route('purchase-returns.show', $return));

    // Stock should decrease from 50 to 45
    $product->refresh();
    expect($product->quantity)->toBe(45);

    // Stock movement should be recorded
    $movement = StockMovement::where('reference', $return->return_number)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->type)->toBe('adjustment_out')
        ->and($movement->quantity)->toBe(5);
});

test('it loads customer ledger and calculates running balance accurately', function () {
    $customer = Customer::create(['name' => 'Khata Customer', 'phone' => '0322-3334455']);

    // Sale of 1000 with 400 paid (600 remaining balance)
    Sale::create([
        'invoice_number' => 'INV-TEST-001',
        'customer_id' => $customer->id,
        'total_amount' => 1000,
        'paid_amount' => 400,
        'change_amount' => 0,
        'payment_method' => 'cash',
    ]);

    $response = $this->get(route('ledgers.customer', ['customer_id' => $customer->id]));
    $response->assertOk();
    $response->assertSee('Khata Customer');
    $response->assertSee('INV-TEST-001');
    $response->assertSee('1,000.00'); // Debit
    $response->assertSee('400.00');   // Credit
    $response->assertSee('600.00');   // Running balance
});

test('it loads vendor ledger and calculates running balance accurately', function () {
    $vendor = Vendor::create(['name' => 'Khata Vendor', 'phone' => '0333-4445566']);

    // Purchase of 2500
    Purchase::create([
        'reference_no' => 'PUR-TEST-001',
        'vendor_id' => $vendor->id,
        'purchase_date' => date('Y-m-d'),
        'total_amount' => 2500,
        'status' => 'received',
    ]);

    $response = $this->get(route('ledgers.vendor', ['vendor_id' => $vendor->id]));
    $response->assertOk();
    $response->assertSee('Khata Vendor');
    $response->assertSee('PUR-TEST-001');
    $response->assertSee('2,500.00'); // Credit payable
});

test('it tests all erp filter query parameters on sales, orders, and purchases', function () {
    $this->get(route('sales.index', ['date_from' => '2026-01-01', 'date_to' => '2026-12-31']))->assertOk();
    $this->get(route('sale-orders.index', ['status' => 'pending']))->assertOk();
    $this->get(route('purchases.index', ['date_from' => '2026-01-01', 'date_to' => '2026-12-31']))->assertOk();
    $this->get(route('purchase-orders.index', ['status' => 'pending']))->assertOk();
});
