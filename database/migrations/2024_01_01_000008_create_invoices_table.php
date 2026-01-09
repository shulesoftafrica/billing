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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->enum('status', ['draft', 'issued', 'paid', 'overdue', 'canceled']);
            $table->string('description');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_total', 15, 2);
            $table->decimal('total', 15, 2);
            $table->date('due_date');
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
