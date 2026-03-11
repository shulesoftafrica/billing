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
        Schema::create('organization_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name')->nullable()->comment('Optional label for the key');
            $table->string('key_prefix', 50)->unique()->comment('Format: org_live_XXXXX or org_test_XXXXX');
            $table->string('key_hash')->unique()->comment('SHA-256 hash of the full API key');
            $table->enum('environment', ['test', 'live'])->default('test');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Optional expiration date');
            $table->enum('status', ['active', 'revoked'])->default('active');
            $table->timestampsTz();
        });

        Schema::table('organization_api_keys', function (Blueprint $table) {
            $table->index('organization_id');
            $table->index('key_prefix');
            $table->index('key_hash');
            $table->index('environment');
            $table->index('status');
            $table->index(['organization_id', 'environment', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_api_keys');
    }
};
