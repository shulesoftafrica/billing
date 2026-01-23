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
        Schema::create('organization_payment_gateway_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->onDelete('restrict');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('status')->default('active');
            $table->json('attachment')->nullable();
            $table->timestampsTz();
        });
        Schema::table('organization_payment_gateway_integrations', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('payment_gateway_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_payment_gateway_integrations');
    }
};
