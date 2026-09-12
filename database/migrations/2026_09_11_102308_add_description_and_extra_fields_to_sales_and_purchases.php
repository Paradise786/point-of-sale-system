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
            if (! Schema::hasColumn('sales', 'description')) {
                $table->text('description')->nullable()->after('note');
            }
            if (! Schema::hasColumn('sales', 'extra_field_one')) {
                $table->string('extra_field_one')->nullable()->after('description');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'description')) {
                $table->text('description')->nullable()->after('note');
            }
            if (! Schema::hasColumn('purchases', 'extra_field_one')) {
                $table->string('extra_field_one')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['description', 'extra_field_one']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['description', 'extra_field_one']);
        });
    }
};
