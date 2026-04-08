<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'currency')) {
                // The DB was provisioned with currency_id (FK) instead of the
                // expected json currency column. Add the json column so the
                // Eloquent model and registration controller work correctly.
                $table->json('currency')->default('["TZS"]')->after('email');
            }
        });

        // Back-fill from currency_id FK where possible
        if (Schema::hasColumn('organizations', 'currency_id')) {
            DB::statement("
                UPDATE organizations o
                SET    currency = COALESCE(
                           (SELECT json_build_array(c.code)
                            FROM   currencies c
                            WHERE  c.id = o.currency_id),
                           '[\"TZS\"]'
                       )
                WHERE  o.currency IS NULL
                   OR  o.currency::text = '\"null\"'
                   OR  o.currency::text = 'null'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};
