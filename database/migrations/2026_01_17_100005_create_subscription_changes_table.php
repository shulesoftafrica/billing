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
        Schema::create('subscription_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('change_type', 50); // 'upgrade', 'downgrade', 'pause', 'reactivate'
            $table->foreignId('old_price_plan_id')->nullable()->constrained('price_plans')->onDelete('set null');
            $table->foreignId('new_price_plan_id')->nullable()->constrained('price_plans')->onDelete('set null');
            $table->decimal('proration_amount', 15, 2)->nullable();
            $table->date('effective_date');
            $table->timestamps();

            $table->index(['subscription_id', 'change_type'], 'idx_subscription_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_changes');
    }
};