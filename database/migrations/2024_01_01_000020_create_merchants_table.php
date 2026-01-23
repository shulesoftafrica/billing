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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_payment_gateway_integration_id')->nullable()->constrained('organization_payment_gateway_integrations')->onDelete('set null');
            $table->json('header_response')->nullable();
            $table->string('merchant_code')->unique();
            $table->text('qr_code')->nullable();
            $table->string('terminal_id', 100)->nullable();
            $table->string('terminal_name')->nullable();
            $table->string('secret_key', 500);
            $table->timestampsTz();
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->index('merchant_code');
            $table->index('organization_payment_gateway_integration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
