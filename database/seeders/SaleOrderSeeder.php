<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use Illuminate\Database\Seeder;

class SaleOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = Customer::whereNotNull('email')->first();
        $product1 = Product::with('unit')->first();
        $product2 = Product::with('unit')->skip(1)->first();

        if ($customer && $product1 && $product2) {
            $soNumber = 'SO-'.date('Ymd').'-DEMO';
            if (! SaleOrder::where('so_number', $soNumber)->exists()) {
                $qty1 = 2;
                $price1 = $product1->selling_price;
                $qty2 = 1;
                $price2 = $product2->selling_price;
                $total = ($qty1 * $price1) + ($qty2 * $price2);

                $so = SaleOrder::create([
                    'so_number' => $soNumber,
                    'customer_id' => $customer->id,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'notes' => 'Sample booking order for client test.',
                ]);

                SaleOrderItem::create([
                    'sale_order_id' => $so->id,
                    'product_id' => $product1->id,
                    'unit_id' => $product1->unit_id,
                    'conversion_rate' => 1.0,
                    'quantity' => $qty1,
                    'unit_price' => $price1,
                    'subtotal' => $qty1 * $price1,
                ]);

                SaleOrderItem::create([
                    'sale_order_id' => $so->id,
                    'product_id' => $product2->id,
                    'unit_id' => $product2->unit_id,
                    'conversion_rate' => 1.0,
                    'quantity' => $qty2,
                    'unit_price' => $price2,
                    'subtotal' => $qty2 * $price2,
                ]);
            }
        }
    }
}
