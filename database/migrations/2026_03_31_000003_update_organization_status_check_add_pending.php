<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing organization status check constraint
        DB::statement('ALTER TABLE organizations DROP CONSTRAINT org_status_check');

        // Re-add with 'pending' status included
        DB::statement("ALTER TABLE organizations ADD CONSTRAINT org_status_check CHECK (status IN ('active', 'inactive', 'suspended', 'pending'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original constraint without 'pending'
        DB::statement('ALTER TABLE organizations DROP CONSTRAINT org_status_check');

        DB::statement("ALTER TABLE organizations ADD CONSTRAINT org_status_check CHECK (status IN ('active', 'inactive', 'suspended'))");
    }
};
