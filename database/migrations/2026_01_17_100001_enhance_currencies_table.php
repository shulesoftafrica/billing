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
        // Check if currencies table exists before trying to alter it
        if (!Schema::hasTable('currencies')) {
            // Create the base currencies table first
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->char('code', 3)->unique();
                $table->string('symbol');
                $table->timestamps();
            });
        }

        // Now add the enhancement columns
        Schema::table('currencies', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('currencies', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            }
            if (!Schema::hasColumn('currencies', 'is_base_currency')) {
                $table->boolean('is_base_currency')->default(false);
            }
            if (!Schema::hasColumn('currencies', 'last_updated')) {
                $table->timestamp('last_updated')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table) {
                if (Schema::hasColumn('currencies', 'exchange_rate')) {
                    $table->dropColumn('exchange_rate');
                }
                if (Schema::hasColumn('currencies', 'is_base_currency')) {
                    $table->dropColumn('is_base_currency');
                }
                if (Schema::hasColumn('currencies', 'last_updated')) {
                    $table->dropColumn('last_updated');
                }
            });
        }
    }
};