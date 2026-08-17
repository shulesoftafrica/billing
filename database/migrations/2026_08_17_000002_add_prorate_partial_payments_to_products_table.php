<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-product opt-in for prorated partial-payment activation. Defaults to
 * false so every existing product (AI credits, LineShop, Talent, Parent
 * Premium App, etc.) keeps its exact current behavior — a partial payment
 * stays correctly-recorded-but-pending, nothing changes for them. Only the
 * ShuleSoft School Subscription product gets this turned on (a separate,
 * explicit data change, not part of this migration), since proration
 * ("recalculate remaining days from a partial payment, re-lock once
 * spent") was a school-subscription-specific requirement, not something
 * every product on this shared platform asked for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('prorate_partial_payments')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('prorate_partial_payments');
        });
    }
};
