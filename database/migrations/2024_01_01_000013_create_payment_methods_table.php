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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('restrict');
            $table->string('token', 500);
            $table->string('type');
            $table->char('last4', 4)->nullable();
            $table->date('expiry')->nullable();
            $table->json('billing_details')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('gateway_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
