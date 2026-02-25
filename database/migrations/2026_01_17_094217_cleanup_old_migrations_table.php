<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old billingmigrations table if it exists
        try {
            DB::statement('DROP TABLE IF EXISTS billing.billingmigrations CASCADE');
            echo "Dropped billing.billingmigrations table\n";
        } catch (Exception $e) {
            echo "Note: billingmigrations table may not exist or already dropped\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse - we don't want to recreate the old table
    }
};
