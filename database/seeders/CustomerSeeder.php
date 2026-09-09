<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Walk-in Customer',   'phone' => null,           'email' => null,                    'address' => null],
            ['name' => 'Ali Khan',           'phone' => '0300-9876543', 'email' => 'ali.khan@email.com',    'address' => 'Model Town, Lahore'],
            ['name' => 'Ayesha Siddiqui',    'phone' => '0321-8765432', 'email' => 'ayesha.s@email.com',   'address' => 'DHA, Karachi'],
            ['name' => 'Ahmed Raza',         'phone' => '0333-7654321', 'email' => 'ahmed.raza@email.com',  'address' => 'F-10, Islamabad'],
            ['name' => 'Sara Malik',         'phone' => '0311-6543210', 'email' => 'sara.malik@email.com',  'address' => 'Bahria Town, Rawalpindi'],
            ['name' => 'Usman Tariq',        'phone' => '0345-5432109', 'email' => 'usman.tariq@email.com', 'address' => 'Cavalry Ground, Lahore'],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['name' => $customer['name']], $customer);
        }
    }
}
