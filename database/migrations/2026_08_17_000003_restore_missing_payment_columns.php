<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * notification_status and payment_reference are both defined in
 * 2024_01_01_000014_create_payments_table and are in Payment::$fillable,
 * but were found missing from the actual local payments table (dropped
 * outside Laravel's migration history — migrate:status still shows that
 * migration as "Ran"). payment_reference specifically is what
 * SubscriptionService::enableSubscription() matches against
 * ControlNumber.reference to recognize a payment at all, so without it
 * the entire payment-matching pipeline silently can't work locally.
 * Guarded with hasColumn so this is a no-op anywhere the columns already
 * exist (e.g. production, if this drift is local-only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'notification_status')) {
                $table->string('notification_status')->default('pending')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['notification_status', 'payment_reference']);
        });
    }
};
