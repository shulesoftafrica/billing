<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensures control_numbers table has required customer_id column
     */
    public function up(): void
    {
        // Check if customer_id column exists
        $hasCustomerId = DB::select("SELECT column_name 
                                       FROM information_schema.columns 
                                       WHERE table_schema = 'billing'
                                       AND table_name='control_numbers' 
                                       AND column_name='customer_id'");
        
        if (empty($hasCustomerId)) {
            // Add customer_id column if missing
            DB::statement('ALTER TABLE control_numbers ADD COLUMN customer_id BIGINT REFERENCES customers(id) ON DELETE SET NULL');
            
            // Create index if not exists
            DB::statement('CREATE INDEX IF NOT EXISTS control_numbers_customer_id_index ON control_numbers(customer_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the column as it's part of original design
        // This migration only ensures it exists
    }
};
