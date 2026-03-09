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
            $table->unique(['name', 'product_id', 'subscription_type'], 'price_plans_name_product_subscription_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            $table->dropUnique('price_plans_name_product_subscription_type_unique');
        });
    }
};
