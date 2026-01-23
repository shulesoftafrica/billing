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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('env')->default('production');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('payment_gateway_id')->constrained('payment_gateways')->onDelete('restrict');
            $table->json('config')->nullable();
            $table->timestampsTz();
        });
        Schema::table('configurations', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('env');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
