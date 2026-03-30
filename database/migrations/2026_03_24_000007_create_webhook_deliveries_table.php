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
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_webhook_id');
            $table->string('event_type', 100);
            $table->text('payload')->comment('JSON payload sent');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->integer('attempt_count')->default(0);
            $table->integer('http_status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('custom_webhook_id')
                  ->references('id')
                  ->on('custom_webhooks')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index(['custom_webhook_id', 'status'], 'idx_webhook_status');
            $table->index(['status', 'next_retry_at'], 'idx_retry');
            $table->index(['event_type', 'status'], 'idx_event_status');
            $table->index('created_at', 'idx_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
