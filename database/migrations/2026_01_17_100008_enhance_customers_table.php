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
            $table->string('customer_type', 20)->default('individual'); // 'individual', 'school', 'organization'
            
            // Add indexes for optimization
            $table->index('phone', 'idx_customers_phone');
            $table->index('email', 'idx_customers_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_phone');
            $table->dropIndex('idx_customers_email');
            $table->dropColumn('customer_type');
        });
    }
};