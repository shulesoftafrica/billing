<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persists the quantity/seats a subscription was created with, since
 * price_plans.amount alone is no longer enough to reconstruct what a
 * subscription bills once POST /v1/subscriptions accepts an optional
 * per-plan quantity. Nullable-with-default keeps every existing row
 * (and every caller that never sends quantity) behaving exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('price_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
