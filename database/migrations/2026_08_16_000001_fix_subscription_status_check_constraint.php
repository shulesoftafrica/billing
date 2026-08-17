<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * subscriptions.status has three overlapping, mutually-conflicting CHECK
 * constraints today (subscriptions_status_check, subscription_status_check,
 * check_subscription_status) — confirmed via pg_constraint. None of them
 * allow 'expired' with the app's own spelling of 'cancelled' (double L,
 * what every write site in this codebase actually uses —
 * SubscriptionService.php, Api/InvoiceController.php). This replaces all
 * three with one constraint matching real app behavior, and makes
 * 'expired' a legal value so subscriptions:renew can mark a superseded
 * subscription as expired once its replacement period is created.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscription_status_check');
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS check_subscription_status');

        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check
            CHECK (status IN ('pending', 'active', 'partial', 'paused', 'cancelled', 'expired'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');

        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT check_subscription_status
            CHECK (status IN ('pending', 'active', 'partial', 'paused', 'canceled'))");
    }
};
