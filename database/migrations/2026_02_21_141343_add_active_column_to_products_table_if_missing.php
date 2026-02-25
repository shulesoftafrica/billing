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
        // Check if products table exists
        if (!Schema::hasTable('products')) {
            throw new \Exception('Products table does not exist. Please run the create_products_table migration first.');
        }

        // Add active column if it doesn't exist
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'active')) {
                $table->boolean('active')->default(true)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
