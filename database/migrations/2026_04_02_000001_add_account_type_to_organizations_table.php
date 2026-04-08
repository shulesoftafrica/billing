<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('account_type')->default('organization')->after('status');
        });

        // Add check constraint for account_type values
        DB::statement("ALTER TABLE organizations ADD CONSTRAINT org_account_type_check CHECK (account_type IN ('organization', 'developer'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE organizations DROP CONSTRAINT IF EXISTS org_account_type_check");

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
