<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cross-system traceability for backfilled/migrated customers: stores the
 * source system's own numeric id (e.g. admin.clients.id from the ShuleSoft
 * staff CRM) alongside the existing `username` match key, so a backfilled
 * record can always be traced back to exactly where it came from — not
 * just by a string match, which could theoretically collide or be renamed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('external_client_id')->nullable()->after('username');
            $table->index('external_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['external_client_id']);
            $table->dropColumn('external_client_id');
        });
    }
};
