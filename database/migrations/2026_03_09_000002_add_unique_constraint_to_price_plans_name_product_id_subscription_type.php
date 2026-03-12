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
        Schema::table('price_plans', function (Blueprint $table) {
            // NOTE: Using billing_type and billing_interval instead of subscription_type
            $table->unique(['name', 'product_id', 'billing_type', 'billing_interval'], 'price_plans_name_product_billing_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            $table->dropUnique('price_plans_name_product_billing_unique');
        });
    }
};
