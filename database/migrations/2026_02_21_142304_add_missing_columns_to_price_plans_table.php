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
        // Check if price_plans table exists
        if (!Schema::hasTable('price_plans')) {
            throw new \Exception('Price plans table does not exist. Please run the create_price_plans_table migration first.');
        }

        Schema::table('price_plans', function (Blueprint $table) {
            // Add billing_type if it doesn't exist
            if (!Schema::hasColumn('price_plans', 'billing_type')) {
                $table->enum('billing_type', ['one_time', 'recurring', 'usage'])
                    ->default('one_time')
                    ->after('product_id');
            }

            // Add billing_interval if it doesn't exist
            if (!Schema::hasColumn('price_plans', 'billing_interval')) {
                $table->enum('billing_interval', ['monthly', 'yearly'])
                    ->nullable()
                    ->after('billing_type');
            }

            // Add active if it doesn't exist
            if (!Schema::hasColumn('price_plans', 'active')) {
                $table->boolean('active')
                    ->default(true)
                    ->after('currency_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            if (Schema::hasColumn('price_plans', 'billing_type')) {
                $table->dropColumn('billing_type');
            }

            if (Schema::hasColumn('price_plans', 'billing_interval')) {
                $table->dropColumn('billing_interval');
            }

            if (Schema::hasColumn('price_plans', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
