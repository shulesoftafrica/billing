<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove product_id from customers table.
 *
 * A customer is scoped to an organisation, not to a single product.
 * The relationship between customers and products is many-to-many and
 * is already represented through subscriptions → price_plans → products.
 *
 * Reverting 2026_03_24_000002, 000003, and 000004 which added this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the named composite index added by migration 000004 first.
        // In PostgreSQL, dropping a column cascades to its indexes, but the
        // composite index (product_id, status) has an explicit name we must
        // release before the column disappears.
        \DB::statement('DROP INDEX IF EXISTS idx_product_status');

        Schema::table('customers', function (Blueprint $table) {
            // Drop the foreign key constraint before the column
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
            $table->index('product_id');
        });
    }
};
