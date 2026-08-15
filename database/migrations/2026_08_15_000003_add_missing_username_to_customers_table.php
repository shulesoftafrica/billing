<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer model ($fillable) and CustomerController (store/update/index
 * validation and filtering) have referenced a `username` column all along,
 * uniquely scoped per organization_id — but no migration in this repo ever
 * actually created it on `customers` (confirmed: it's genuinely absent from
 * the live table, and grepping the whole app for "username" only turns up
 * these existing read/write sites, never a migration). Any API call that
 * sets `username` today fails outright (its own uniqueness-validation query
 * errors on the missing column before even reaching the insert). This adds
 * the column the rest of the codebase already assumes exists — a pure gap
 * fill, not new behavior.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->unique(['organization_id', 'username'], 'customers_org_username_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_org_username_unique');
            $table->dropColumn('username');
        });
    }
};
