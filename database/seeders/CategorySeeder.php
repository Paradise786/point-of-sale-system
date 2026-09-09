<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics',       'description' => 'Electronic devices and accessories'],
            ['name' => 'Food & Beverages',  'description' => 'Edible products and drinks'],
            ['name' => 'Clothing & Apparel', 'description' => 'Garments and fashion items'],
            ['name' => 'Accessories',       'description' => 'Bags, watches, and other accessories'],
            ['name' => 'Stationery',        'description' => 'Office and school supplies'],
            ['name' => 'Household',         'description' => 'Home and kitchen items'],
            ['name' => 'Health & Beauty',   'description' => 'Personal care and wellness products'],
            ['name' => 'Sports & Fitness',  'description' => 'Sporting goods and equipment'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
