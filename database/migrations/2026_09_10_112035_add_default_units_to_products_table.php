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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('default_sale_unit_id')->nullable()->after('unit_id')->constrained('units')->nullOnDelete();
            $table->foreignId('default_purchase_unit_id')->nullable()->after('default_sale_unit_id')->constrained('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['default_sale_unit_id']);
            $table->dropForeign(['default_purchase_unit_id']);
            $table->dropColumn(['default_sale_unit_id', 'default_purchase_unit_id']);
        });
    }
};
