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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('notification_status')->default('pending');
            $table->string('gateway_reference')->unique();
            $table->string('status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestampsTz();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('customer_id');
            $table->index('gateway_id');
            $table->index('notification_status');
            $table->index('gateway_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
