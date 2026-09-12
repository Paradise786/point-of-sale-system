<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::first();
        $product1 = Product::with('unit')->first();
        $product2 = Product::with('unit')->skip(1)->first();

        if ($vendor && $product1 && $product2) {
            $poNumber = 'PO-'.date('Ymd').'-DEMO';
            if (! PurchaseOrder::where('po_number', $poNumber)->exists()) {
                $qty1 = 10;
                $price1 = $product1->purchase_price;
                $qty2 = 5;
                $price2 = $product2->purchase_price;
                $total = ($qty1 * $price1) + ($qty2 * $price2);

                $po = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'vendor_id' => $vendor->id,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'notes' => 'Sample supplier purchase order test.',
                ]);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product1->id,
                    'unit_id' => $product1->unit_id,
                    'conversion_rate' => 1.0,
                    'quantity' => $qty1,
                    'unit_price' => $price1,
                    'subtotal' => $qty1 * $price1,
                ]);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
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
