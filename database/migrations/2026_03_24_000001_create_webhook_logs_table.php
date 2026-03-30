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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique()->index();
            $table->enum('webhook_type', ['stripe', 'flutterwave', 'ucn'])->index();
            $table->enum('status', ['in_progress', 'completed', 'error'])->default('in_progress')->index();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->decimal('duration_ms', 10, 2)->nullable();
            $table->integer('http_status_code')->nullable();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('created_at');
            $table->index(['webhook_type', 'status']);
            $table->index(['webhook_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
