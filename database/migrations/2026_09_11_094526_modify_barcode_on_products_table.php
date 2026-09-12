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
            // Drop the unique index first if it already exists (idempotent)
            try {
                $table->dropUnique(['barcode']);
            } catch (Exception $e) {
                // Index may not exist yet – that's fine
            }
            $table->string('barcode')->nullable()->change();
            $table->unique('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
