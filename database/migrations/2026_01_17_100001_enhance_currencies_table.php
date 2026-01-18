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
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->boolean('is_base_currency')->default(false);
            $table->timestamp('last_updated')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'is_base_currency', 'last_updated']);
        });
    }
};