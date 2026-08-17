<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * payments.status has two overlapping, conflicting CHECK constraints
 * (payments_status_check from the original create-table migration, and
 * payment_status_check from 2026_01_23_000000_add_check_constraints) —
 * neither allows 'cleared', which is the value SubscriptionService
 * actually writes at every payment-matching call site (60+ occurrences).
 * Only 'pending' and 'cleared' are ever written to this column anywhere
 * in the app. Same drift pattern already fixed for subscriptions.status
 * in 2026_08_16_000001_fix_subscription_status_check_constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payment_status_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payment_status_check CHECK (status IN ('pending', 'cleared', 'failed', 'cancelled'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payment_status_check');
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending', 'success', 'failed'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payment_status_check CHECK (status IN ('pending', 'processing', 'completed', 'failed', 'cancelled'))");
    }
};
