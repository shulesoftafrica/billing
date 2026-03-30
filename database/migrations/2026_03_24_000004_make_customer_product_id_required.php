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
        Schema::table('customers', function (Blueprint $table) {
            // Make product_id NOT NULL after data migration
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            
            // Add composite index for common queries
            $table->index(['product_id', 'status'], 'idx_product_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_product_status');
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });
    }
};
