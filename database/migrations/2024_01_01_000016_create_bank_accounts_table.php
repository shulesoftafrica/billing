<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->string('branch');
            $table->unsignedBigInteger('refer_bank_id')->nullable(); // Make nullable for now
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->timestamps();
        });

        // TODO: Add foreign key constraint when constant.refer_banks table is available
        // DB::statement('ALTER TABLE billing.bank_accounts ADD CONSTRAINT bank_accounts_refer_bank_id_foreign FOREIGN KEY (refer_bank_id) REFERENCES constant.refer_banks(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
