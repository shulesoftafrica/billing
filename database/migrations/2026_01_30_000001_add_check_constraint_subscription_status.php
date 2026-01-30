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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('status')
                ->change()
                ->comment('Status: pending, active, partial, paused, canceled');
        });

        // Add check constraint for status
        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT check_subscription_status CHECK (status IN ('pending', 'active', 'partial', 'paused', 'canceled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE subscriptions DROP CONSTRAINT check_subscription_status");
    }
};
