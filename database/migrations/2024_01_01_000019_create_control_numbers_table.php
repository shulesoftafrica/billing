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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->foreignId('organization_payment_gateway_integration_id')->constrained('organization_payment_gateway_integrations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('type_id')->default(9);
            $table->json('header_response')->nullable();
            $table->text('qr_code')->nullable();
            $table->integer('notified')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
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
