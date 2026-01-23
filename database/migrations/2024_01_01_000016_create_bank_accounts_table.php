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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->unsignedBigInteger('refer_bank_id')->nullable();
            $table->foreign('refer_bank_id')->references('id')->on('constant.refer_banks')->onDelete('set null');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->timestampsTz();
        });
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
