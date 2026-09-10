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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('sale_order_id')->nullable()->after('id')->constrained('sale_orders')->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('id')->constrained('purchase_orders')->nullOnDelete();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->decimal('conversion_rate', 12, 4)->default(1)->after('unit_id');
            $table->decimal('base_quantity', 12, 4)->default(1)->after('quantity');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->decimal('conversion_rate', 12, 4)->default(1)->after('unit_id');
            $table->decimal('base_quantity', 12, 4)->default(1)->after('quantity');
        });

        Schema::table('sale_order_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->decimal('conversion_rate', 12, 4)->default(1)->after('unit_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->decimal('conversion_rate', 12, 4)->default(1)->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_rate']);
        });

        Schema::table('sale_order_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_rate']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_rate', 'base_quantity']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'conversion_rate', 'base_quantity']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['purchase_order_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['sale_order_id']);
            $table->dropColumn(['sale_order_id']);
        });
    }
};
