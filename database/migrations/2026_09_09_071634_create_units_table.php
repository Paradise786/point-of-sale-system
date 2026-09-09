<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Piece, Box, Carton, Dozen, Kg
            $table->string('short_code', 20); // e.g. pc, box, ctn, dz, kg
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete(); // if null, it is base unit
            $table->enum('operator', ['*', '/'])->default('*'); // multiplication or division to get base unit
            $table->decimal('conversion_factor', 12, 4)->default(1); // e.g. 1 Box = 10 Pieces -> factor 10
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('category_id')->constrained('units')->nullOnDelete();
            $table->string('sku')->nullable()->after('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'sku']);
        });

        Schema::dropIfExists('units');
    }
};
