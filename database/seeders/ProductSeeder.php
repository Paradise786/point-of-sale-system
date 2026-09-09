<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('name', 'Electronics')->value('id');
        $food = Category::where('name', 'Food & Beverages')->value('id');
        $clothing = Category::where('name', 'Clothing & Apparel')->value('id');
        $accessories = Category::where('name', 'Accessories')->value('id');
        $stationery = Category::where('name', 'Stationery')->value('id');

        $pcs = Unit::where('short_code', 'pc')->value('id');
        $kg = Unit::where('short_code', 'kg')->value('id');
        $ltr = Unit::where('short_code', 'ltr')->value('id');
        $box = Unit::where('short_code', 'box')->value('id');
        $dozen = Unit::where('short_code', 'dz')->value('id');

        $products = [
            [
                'name' => 'Wireless Mouse',
                'sku' => 'ELEC-001',
                'barcode' => 'BC-ELEC-001',
                'description' => 'Ergonomic wireless mouse with USB receiver',
                'purchase_price' => 800,
                'selling_price' => 1200,
                'quantity' => 50,
                'alert_quantity' => 5,
                'category_id' => $electronics,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'Smartphone 6.5"',
                'sku' => 'ELEC-002',
                'barcode' => 'BC-ELEC-002',
                'description' => 'Android smartphone with 128GB storage',
                'purchase_price' => 35000,
                'selling_price' => 42000,
                'quantity' => 20,
                'alert_quantity' => 3,
                'category_id' => $electronics,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'Laptop 15"',
                'sku' => 'ELEC-003',
                'barcode' => 'BC-ELEC-003',
                'description' => 'Core i5 laptop with 8GB RAM, 256GB SSD',
                'purchase_price' => 80000,
                'selling_price' => 95000,
                'quantity' => 10,
                'alert_quantity' => 2,
                'category_id' => $electronics,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'USB-C Charger',
                'sku' => 'ELEC-004',
                'barcode' => 'BC-ELEC-004',
                'description' => '65W fast charger with USB-C cable',
                'purchase_price' => 1200,
                'selling_price' => 1800,
                'quantity' => 100,
                'alert_quantity' => 10,
                'category_id' => $electronics,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'Basmati Rice',
                'sku' => 'FOOD-001',
                'barcode' => 'BC-FOOD-001',
                'description' => 'Premium quality Basmati rice',
                'purchase_price' => 150,
                'selling_price' => 200,
                'quantity' => 500,
                'alert_quantity' => 50,
                'category_id' => $food,
                'unit_id' => $kg,
            ],
            [
                'name' => 'Mineral Water 1L',
                'sku' => 'FOOD-002',
                'barcode' => 'BC-FOOD-002',
                'description' => 'Purified mineral water, 1 litre bottle',
                'purchase_price' => 25,
                'selling_price' => 40,
                'quantity' => 300,
                'alert_quantity' => 30,
                'category_id' => $food,
                'unit_id' => $ltr,
            ],
            [
                'name' => "Men's T-Shirt",
                'sku' => 'CLO-001',
                'barcode' => 'BC-CLO-001',
                'description' => '100% cotton casual t-shirt',
                'purchase_price' => 400,
                'selling_price' => 700,
                'quantity' => 150,
                'alert_quantity' => 10,
                'category_id' => $clothing,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'Leather Wallet',
                'sku' => 'ACC-001',
                'barcode' => 'BC-ACC-001',
                'description' => 'Genuine leather bifold wallet',
                'purchase_price' => 600,
                'selling_price' => 1100,
                'quantity' => 60,
                'alert_quantity' => 5,
                'category_id' => $accessories,
                'unit_id' => $pcs,
            ],
            [
                'name' => 'A4 Paper Box',
                'sku' => 'STA-001',
                'barcode' => 'BC-STA-001',
                'description' => 'Box of 500 sheets A4 80gsm paper',
                'purchase_price' => 900,
                'selling_price' => 1300,
                'quantity' => 40,
                'alert_quantity' => 5,
                'category_id' => $stationery,
                'unit_id' => $box,
            ],
            [
                'name' => 'Ball Pen (Dozen)',
                'sku' => 'STA-002',
                'barcode' => 'BC-STA-002',
                'description' => 'Blue ball pens, pack of 12',
                'purchase_price' => 120,
                'selling_price' => 200,
                'quantity' => 200,
                'alert_quantity' => 20,
                'category_id' => $stationery,
                'unit_id' => $dozen,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['barcode' => $product['barcode']], $product);
        }
    }
}
