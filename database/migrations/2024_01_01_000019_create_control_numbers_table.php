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
        Schema::create('control_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('reference')->unique();
            $table->foreignId('organization_payment_gateway_integration_id')->nullable()->constrained('organization_payment_gateway_integrations')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestampsTz();
        });
        Schema::table('control_numbers', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('reference');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_numbers');
    }
};
