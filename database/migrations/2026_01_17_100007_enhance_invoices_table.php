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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
            $table->string('invoice_type', 50)->default('subscription'); // 'subscription', 'wallet_topup', 'plan_upgrade', 'one_time'
            $table->decimal('proration_credit', 15, 2)->default(0.00);
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'invoice_type', 'proration_credit', 'metadata']);
        });
    }
};