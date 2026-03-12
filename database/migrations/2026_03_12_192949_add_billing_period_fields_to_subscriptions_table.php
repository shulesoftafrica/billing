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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->date('current_period_start')->nullable()->after('start_date');
            $table->date('current_period_end')->nullable()->after('current_period_start');
            $table->string('previous_plan_id')->nullable()->after('price_plan_id')->comment('For tracking upgrades/downgrades');
            $table->decimal('last_upgrade_proration', 15, 2)->default(0.00)->comment('Amount charged for last upgrade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['current_period_start', 'current_period_end', 'previous_plan_id', 'last_upgrade_proration']);
        });
    }
};
