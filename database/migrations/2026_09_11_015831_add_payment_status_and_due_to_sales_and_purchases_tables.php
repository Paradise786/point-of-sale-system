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
            $table->string('payment_status')->default('paid')->after('payment_method');
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
            $table->string('payment_status')->default('unpaid')->after('due_amount');
            $table->string('payment_method')->default('cash')->after('payment_status');
        });

        // Backfill existing sales
        DB::table('sales')->get()->each(function ($sale) {
            $total = (float) $sale->total_amount;
            $paid = (float) $sale->paid_amount;
            $due = max(0, $total - $paid);
            $status = 'paid';
            if ($paid <= 0) {
                $status = 'unpaid';
            } elseif ($paid < $total) {
                $status = 'partially_paid';
            }
            DB::table('sales')->where('id', $sale->id)->update([
                'payment_status' => $status,
                'due_amount' => $due,
            ]);
        });

        // Backfill existing purchases
        DB::table('purchases')->get()->each(function ($p) {
            DB::table('purchases')->where('id', $p->id)->update([
                'paid_amount' => $p->total_amount,
                'due_amount' => 0,
                'payment_status' => 'paid',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'due_amount']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount', 'payment_status', 'payment_method']);
        });
    }
};
