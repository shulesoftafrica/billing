<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Restore organization_id to customers table for backward compatibility.
     * The organization_id will be derived from the product's organization.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add organization_id column back (nullable initially for data migration)
            $table->unsignedBigInteger('organization_id')->nullable()->after('product_id');
        });

        // Populate organization_id from products table for customers that have a product (PostgreSQL syntax)
        DB::statement("
            UPDATE customers c
            SET organization_id = p.organization_id
            FROM products p
            WHERE p.id = c.product_id
            AND c.product_id IS NOT NULL
        ");

        // For customers without a product_id, we cannot derive organization_id
        // These should be handled by the application logic when creating customers
        // Make organization_id NOT nullable only after setting foreign key
        
        Schema::table('customers', function (Blueprint $table) {
            // Add foreign key constraint (organization_id can still be NULL for customers without products)
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            // Add index for better query performance
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            
            // Drop the column
            $table->dropColumn('organization_id');
        });
    }
};
