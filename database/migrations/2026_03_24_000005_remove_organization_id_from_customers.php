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
            // Drop foreign key constraint first
            $table->dropForeign(['organization_id']);
            
            // Drop the column
            $table->dropColumn('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Re-add organization_id column
            $table->unsignedBigInteger('organization_id')->nullable()->after('product_id');
            
            // Re-add foreign key
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade');
            
            // Populate from product relationship
            \DB::statement("
                UPDATE customers c
                INNER JOIN products p ON p.id = c.product_id
                SET c.organization_id = p.organization_id
            ");
        });
    }
};
