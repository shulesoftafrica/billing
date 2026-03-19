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
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name')->comment('Human-readable name for the client');
            $table->string('client_id', 64)->unique()->comment('Public client identifier');
            $table->string('client_secret_hash')->comment('SHA-256 hash of client secret');
            $table->string('client_secret_prefix', 12)->index()->comment('First 12 chars of secret for lookup');
            $table->enum('environment', ['test', 'live'])->default('test');
            $table->enum('status', ['active', 'revoked', 'suspended'])->default('active');
            $table->text('allowed_scopes')->nullable()->comment('JSON array of allowed scopes');
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Optional expiration date');
            $table->timestamps();

            // Indexes for performance
            $table->index(['organization_id', 'status']);
            $table->index('environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
};
