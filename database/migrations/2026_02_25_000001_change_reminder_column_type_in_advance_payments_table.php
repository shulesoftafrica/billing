<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE advance_payments
            ALTER COLUMN reminder TYPE DECIMAL(12,2),
            ALTER COLUMN reminder SET NOT NULL,
            ALTER COLUMN reminder SET DEFAULT 0
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE advance_payments
            ALTER COLUMN reminder TYPE BIGINT,
            ALTER COLUMN reminder SET NOT NULL,
            ALTER COLUMN reminder SET DEFAULT 0
        ");
    }
};
