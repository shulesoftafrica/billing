<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Check if currencies table exists
        if (!Schema::hasTable('currencies')) {
            throw new \Exception('Currencies table does not exist. Please run currency migrations first.');
        }

        Schema::table('price_plans', function (Blueprint $table) {
            // Add currency_id if it doesn't exist
            if (!Schema::hasColumn('price_plans', 'currency_id')) {
                // Get the first currency ID as default
                $defaultCurrencyId = DB::table('currencies')->value('id');
                
                if (!$defaultCurrencyId) {
                    throw new \Exception('No currencies found in database. Please seed currencies first.');
                }

                $table->foreignId('currency_id')
                    ->default($defaultCurrencyId)
                    ->after('amount')
                    ->constrained('currencies')
                    ->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            if (Schema::hasColumn('price_plans', 'currency_id')) {
                $table->dropForeign(['currency_id']);
                $table->dropColumn('currency_id');
            }
        });
    }
};
