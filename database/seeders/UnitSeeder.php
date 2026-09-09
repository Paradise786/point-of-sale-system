<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Base Units
        $piece = Unit::firstOrCreate(['short_code' => 'pc'], [
            'name' => 'Piece',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $kg = Unit::firstOrCreate(['short_code' => 'kg'], [
            'name' => 'Kilogram',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $liter = Unit::firstOrCreate(['short_code' => 'ltr'], [
            'name' => 'Liter',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $meter = Unit::firstOrCreate(['short_code' => 'm'], [
            'name' => 'Meter',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        // 2. Derived Units (Conversions)
        Unit::firstOrCreate(['short_code' => 'box'], [
            'name' => 'Box (10 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 10,
        ]);

        Unit::firstOrCreate(['short_code' => 'ctn'], [
            'name' => 'Carton (24 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 24,
        ]);

        Unit::firstOrCreate(['short_code' => 'dz'], [
            'name' => 'Dozen (12 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 12,
        ]);

        Unit::firstOrCreate(['short_code' => 'pack'], [
            'name' => 'Pack (6 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 6,
        ]);

        Unit::firstOrCreate(['short_code' => 'g'], [
            'name' => 'Gram',
            'base_unit_id' => $kg->id,
            'operator' => '/',
            'conversion_factor' => 1000,
        ]);

        Unit::firstOrCreate(['short_code' => 'ml'], [
            'name' => 'Milliliter',
            'base_unit_id' => $liter->id,
            'operator' => '/',
            'conversion_factor' => 1000,
        ]);
    }
}
