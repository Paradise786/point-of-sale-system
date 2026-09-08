<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PosDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for SQLite compatibility
        DB::statement('PRAGMA foreign_keys = OFF');

        // Truncate tables
        DB::table('sale_items')->truncate();
        DB::table('sales')->truncate();
        DB::table('purchase_items')->truncate();
        DB::table('purchases')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        DB::table('customers')->truncate();
        DB::table('vendors')->truncate();

        // Categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic gadgets and devices'],
            ['name' => 'Groceries', 'description' => 'Everyday food and household items'],
            ['name' => 'Accessories', 'description' => 'Phone cases, chargers, etc.'],
            ['name' => 'Beverages', 'description' => 'Soft drinks, juices, water'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Products (10 sample)
        $products = [
            ['name' => 'Smartphone X1', 'barcode' => '100001', 'category_id' => 1, 'purchase_price' => 25000, 'selling_price' => 30000, 'quantity' => 50, 'alert_quantity' => 5, 'description' => 'Flagship smartphone'],
            ['name' => 'Laptop Pro 15', 'barcode' => '100002', 'category_id' => 1, 'purchase_price' => 60000, 'selling_price' => 75000, 'quantity' => 30, 'alert_quantity' => 3, 'description' => 'High‑performance laptop'],
            ['name' => 'Wireless Earbuds', 'barcode' => '100003', 'category_id' => 1, 'purchase_price' => 1500, 'selling_price' => 2000, 'quantity' => 120, 'alert_quantity' => 10, 'description' => 'Bluetooth earbuds'],
            ['name' => 'Rice 5kg Bag', 'barcode' => '200001', 'category_id' => 2, 'purchase_price' => 300, 'selling_price' => 400, 'quantity' => 200, 'alert_quantity' => 20, 'description' => 'Premium basmati rice'],
            ['name' => 'Olive Oil 1L', 'barcode' => '200002', 'category_id' => 2, 'purchase_price' => 500, 'selling_price' => 650, 'quantity' => 80, 'alert_quantity' => 8, 'description' => 'Extra virgin olive oil'],
            ['name' => 'Phone Case Black', 'barcode' => '300001', 'category_id' => 3, 'purchase_price' => 120, 'selling_price' => 180, 'quantity' => 150, 'alert_quantity' => 15, 'description' => 'Durable silicone case'],
            ['name' => 'USB‑C Cable 1m', 'barcode' => '300002', 'category_id' => 3, 'purchase_price' => 80, 'selling_price' => 120, 'quantity' => 300, 'alert_quantity' => 30, 'description' => 'Fast charging cable'],
            ['name' => 'Cola 500ml', 'barcode' => '400001', 'category_id' => 4, 'purchase_price' => 30, 'selling_price' => 45, 'quantity' => 400, 'alert_quantity' => 40, 'description' => 'Carbonated soft drink'],
            ['name' => 'Orange Juice 1L', 'barcode' => '400002', 'category_id' => 4, 'purchase_price' => 70, 'selling_price' => 95, 'quantity' => 120, 'alert_quantity' => 12, 'description' => 'Freshly squeezed'],
            ['name' => 'Mineral Water 500ml', 'barcode' => '400003', 'category_id' => 4, 'purchase_price' => 20, 'selling_price' => 30, 'quantity' => 500, 'alert_quantity' => 50, 'description' => 'Pure spring water'],
        ];
        foreach ($products as $prod) {
            Product::create($prod);
        }

        // Customers (5 sample)
        $customers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '555-0101', 'address' => '123 Main St'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'phone' => '555-0202', 'address' => '456 Oak Ave'],
            ['name' => 'Carol Lee', 'email' => 'carol@example.com', 'phone' => '555-0303', 'address' => '789 Pine Rd'],
            ['name' => 'David Miller', 'email' => 'david@example.com', 'phone' => '555-0404', 'address' => '321 Maple Blvd'],
            ['name' => 'Eve Davis', 'email' => 'eve@example.com', 'phone' => '555-0505', 'address' => '654 Cedar Ln'],
        ];
        foreach ($customers as $cust) {
            Customer::create($cust);
        }

        // Vendors (3 sample)
        $vendors = [
            ['name' => 'TechSupply Ltd.', 'email' => 'contact@techsupply.com', 'phone' => '555-1000', 'address' => '100 Industrial Park'],
            ['name' => 'FreshFoods Co.', 'email' => 'sales@freshfoods.co', 'phone' => '555-2000', 'address' => '200 Market Street'],
            ['name' => 'AccessoryHub', 'email' => 'info@accessoryhub.com', 'phone' => '555-3000', 'address' => '300 Commerce Way'],
        ];
        foreach ($vendors as $ven) {
            Vendor::create($ven);
        }

        // Sample Purchases (2 purchases)
        $purchase1 = Purchase::create([
            'reference_no' => 'PO-'.date('Ymd').'-'.Str::upper(Str::random(4)),
            'vendor_id' => 1,
            'purchase_date' => Carbon::now()->subDays(5)->toDateString(),
            'total_amount' => 0,
            'status' => 'received',
            'note' => 'Initial stock for electronics',
        ]);
        $items1 = [
            ['product_id' => 1, 'quantity' => 20, 'purchase_price' => 25000, 'subtotal' => 20 * 25000],
            ['product_id' => 2, 'quantity' => 10, 'purchase_price' => 60000, 'subtotal' => 10 * 60000],
        ];
        $total1 = 0;
        foreach ($items1 as $item) {
            PurchaseItem::create(array_merge(['purchase_id' => $purchase1->id], $item));
            $total1 += $item['subtotal'];
            Product::where('id', $item['product_id'])->increment('quantity', $item['quantity']);
        }
        $purchase1->update(['total_amount' => $total1]);

        $purchase2 = Purchase::create([
            'reference_no' => 'PO-'.date('Ymd').'-'.Str::upper(Str::random(4)),
            'vendor_id' => 2,
            'purchase_date' => Carbon::now()->subDays(2)->toDateString(),
            'total_amount' => 0,
            'status' => 'received',
            'note' => 'Groceries stock',
        ]);
        $items2 = [
            ['product_id' => 4, 'quantity' => 50, 'purchase_price' => 300, 'subtotal' => 50 * 300],
            ['product_id' => 5, 'quantity' => 30, 'purchase_price' => 500, 'subtotal' => 30 * 500],
        ];
        $total2 = 0;
        foreach ($items2 as $item) {
            PurchaseItem::create(array_merge(['purchase_id' => $purchase2->id], $item));
            $total2 += $item['subtotal'];
            Product::where('id', $item['product_id'])->increment('quantity', $item['quantity']);
        }
        $purchase2->update(['total_amount' => $total2]);

        // Sample Sales (2 sales)
        $sale1 = Sale::create([
            'invoice_number' => 'INV-'.date('Ymd').'-'.Str::upper(Str::random(4)),
            'customer_id' => 1,
            'total_amount' => 0,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'note' => 'First sale example',
        ]);
        $saleItems1 = [
            ['product_id' => 1, 'quantity' => 1, 'price' => 30000, 'subtotal' => 30000],
            ['product_id' => 3, 'quantity' => 2, 'price' => 2000, 'subtotal' => 4000],
        ];
        $saleTotal1 = 0;
        foreach ($saleItems1 as $item) {
            SaleItem::create(array_merge(['sale_id' => $sale1->id], $item));
            $saleTotal1 += $item['subtotal'];
            Product::where('id', $item['product_id'])->decrement('quantity', $item['quantity']);
        }
        $sale1->update([
            'total_amount' => $saleTotal1,
            'paid_amount' => $saleTotal1,
            'change_amount' => 0,
        ]);

        $sale2 = Sale::create([
            'invoice_number' => 'INV-'.date('Ymd').'-'.Str::upper(Str::random(4)),
            'customer_id' => null,
            'total_amount' => 0,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_method' => 'card',
            'note' => 'Walk‑in customer purchase',
        ]);
        $saleItems2 = [
            ['product_id' => 4, 'quantity' => 3, 'price' => 400, 'subtotal' => 1200],
            ['product_id' => 8, 'quantity' => 2, 'price' => 45, 'subtotal' => 90],
        ];
        $saleTotal2 = 0;
        foreach ($saleItems2 as $item) {
            SaleItem::create(array_merge(['sale_id' => $sale2->id], $item));
            $saleTotal2 += $item['subtotal'];
            Product::where('id', $item['product_id'])->decrement('quantity', $item['quantity']);
        }
        $sale2->update([
            'total_amount' => $saleTotal2,
            'paid_amount' => $saleTotal2,
            'change_amount' => 0,
        ]);

        // Re‑enable foreign keys
        DB::statement('PRAGMA foreign_keys = ON');
    }
}
?>
