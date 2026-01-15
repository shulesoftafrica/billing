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
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('cascade');
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed']);
            $table->string('attachment')->nullable();
            $table->string('attachment_url')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
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
