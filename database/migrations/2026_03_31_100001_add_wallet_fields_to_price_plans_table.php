<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            // Wallet/credit product fields (only populated when product_type_id = 3)
            $table->string('wallet_type', 100)->nullable()->after('rate')
                ->comment('Type of wallet credits e.g. ai_credits, sms_credits, tokens');
            $table->string('unit', 100)->nullable()->after('wallet_type')
                ->comment('Human-readable unit label e.g. credit, message, token');
            $table->unsignedInteger('units')->nullable()->after('unit')
                ->comment('Number of units included in this plan (used to derive unit_price = amount / units)');
        });
    }

    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            $table->dropColumn(['wallet_type', 'unit', 'units']);
        });
    }
};
