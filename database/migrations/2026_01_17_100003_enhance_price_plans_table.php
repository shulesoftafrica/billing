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
        Schema::table('price_plans', function (Blueprint $table) {
            $table->string('plan_code', 50)->unique()->nullable();
            $table->string('feature_code', 100)->nullable();
            $table->integer('trial_period_days')->default(0);
            $table->decimal('setup_fee', 15, 2)->default(0.00);
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_plans', function (Blueprint $table) {
            $table->dropColumn(['plan_code', 'feature_code', 'trial_period_days', 'setup_fee', 'metadata']);
        });
    }
};