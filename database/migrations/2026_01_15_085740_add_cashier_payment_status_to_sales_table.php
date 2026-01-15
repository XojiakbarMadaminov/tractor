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
            $table->foreignId('cashier_id')
                ->nullable()
                ->after('store_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('payment_type', 25)
                ->default('cash')
                ->after('customer_name');
            $table->string('status', 25)
                ->default('completed')
                ->after('payment_type');
            $table->decimal('paid_amount', 13, 2)
                ->nullable()
                ->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cashier_id');
            $table->dropColumn(['payment_type', 'status', 'paid_amount']);
        });
    }
};
