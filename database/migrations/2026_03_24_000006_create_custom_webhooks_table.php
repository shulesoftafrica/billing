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
        Schema::create('custom_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name');
            $table->string('url', 500);
            $table->string('secret');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('events')->nullable()->comment('Array of subscribed events. Null = all events');
            $table->string('http_method', 10)->default('POST');
            $table->json('headers')->nullable()->comment('Custom HTTP headers');
            $table->integer('timeout')->default(30)->comment('Request timeout in seconds');
            $table->integer('retry_count')->default(3)->comment('Max retry attempts');
            $table->boolean('verify_ssl')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index(['product_id', 'status'], 'idx_custom_webhooks_product_status');
            $table->unique(['product_id', 'url'], 'unique_custom_webhook_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_webhooks');
    }
};
