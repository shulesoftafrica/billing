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
            $table->foreignId('organization_payment_gateway_integration_id')->constrained('organization_payment_gateway_integrations')->onDelete('cascade');
            $table->json('header_response')->nullable();
            $table->string('merchant_code')->nullable();
            $table->text('qr_code')->nullable();
            $table->bigInteger('terminal_id')->nullable();
            $table->string('terminal_name')->nullable();
            $table->string('secret_key')->nullable();
            $table->timestamps();
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
