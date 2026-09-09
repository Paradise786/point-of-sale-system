<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            ['name' => 'TechSupply Ltd.',    'phone' => '0300-1111111', 'email' => 'info@techsupply.pk',    'address' => 'Hall Road, Lahore'],
            ['name' => 'FreshFoods Co.',     'phone' => '0321-2222222', 'email' => 'orders@freshfoods.pk',  'address' => 'Johar Town, Lahore'],
            ['name' => 'AccessoryHub',       'phone' => '0333-3333333', 'email' => 'sales@accessoryhub.pk', 'address' => 'Gulberg, Lahore'],
            ['name' => 'StyleMart Traders',  'phone' => '0311-4444444', 'email' => 'contact@stylemart.pk',  'address' => 'Saddar, Karachi'],
            ['name' => 'OfficeWorld Supplies', 'phone' => '0345-5555555', 'email' => 'supply@officeworld.pk', 'address' => 'Blue Area, Islamabad'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(['name' => $vendor['name']], $vendor);
        }
    }
}
