<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'superseded' as a valid status value.
     *
     * PostgreSQL enums in Laravel are implemented as CHECK constraints.
     * We drop the existing constraint and replace it with one that
     * includes the new value.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE webhook_deliveries DROP CONSTRAINT IF EXISTS webhook_deliveries_status_check");
        DB::statement("ALTER TABLE webhook_deliveries ADD CONSTRAINT webhook_deliveries_status_check CHECK (status::text = ANY (ARRAY['pending'::text, 'sent'::text, 'failed'::text, 'superseded'::text]))");
    }

    public function down(): void
    {
        // Remove any superseded rows before restoring the old constraint
        DB::table('webhook_deliveries')
            ->where('status', 'superseded')
            ->update(['status' => 'failed']);

        DB::statement("ALTER TABLE webhook_deliveries DROP CONSTRAINT IF EXISTS webhook_deliveries_status_check");
        DB::statement("ALTER TABLE webhook_deliveries ADD CONSTRAINT webhook_deliveries_status_check CHECK (status::text = ANY (ARRAY['pending'::text, 'sent'::text, 'failed'::text]))");
    }
};
