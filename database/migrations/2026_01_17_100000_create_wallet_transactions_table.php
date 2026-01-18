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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('wallet_type', 50); // 'balance', 'points', 'credits'
            $table->string('transaction_type', 30); // 'topup', 'deduction', 'transfer', 'refund'
            $table->decimal('units', 15, 4);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->string('reference_number', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('completed'); // 'pending', 'completed', 'failed'
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['customer_id', 'wallet_type'], 'idx_customer_wallet');
            $table->index('transaction_type', 'idx_transaction_type');
            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};