<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            VendorSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            SaleOrderSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}
