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
        Schema::table('products', function (Blueprint $table) {
            // Add product_code field only if it doesn't exist
            if (!Schema::hasColumn('products', 'product_code')) {
                $table->string('product_code')->after('name');
            }
            
            // Create composite unique index for organization_id + product_code
            $table->unique(['organization_id', 'product_code'], 'products_org_code_unique');
            
            // Create composite unique index for organization_id + name
            $table->unique(['organization_id', 'name'], 'products_org_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop unique indexes first
            $table->dropUnique('products_org_code_unique');
            $table->dropUnique('products_org_name_unique');
            
            // Drop the product_code column
            $table->dropColumn('product_code');
        });
    }
};
