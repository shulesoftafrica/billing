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
        Schema::create('organization_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('document_name');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->default('application/pdf');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestampsTz();
        });

        Schema::table('organization_documents', function (Blueprint $table) {
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_documents');
    }
};
