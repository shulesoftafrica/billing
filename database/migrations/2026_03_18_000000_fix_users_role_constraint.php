<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix user constraints to match application validation logic.
     */
    public function up(): void
    {
        // Fix sex constraint - allow NULL, M, F, and O
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS user_sex_check');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_sex_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_sex_check CHECK (sex IS NULL OR sex IN ('M', 'F', 'O'))");
        
        // Fix role constraint - allow NULL, admin, user, manager, finance, support
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IS NULL OR role IN ('admin', 'user', 'manager', 'finance', 'support'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old constraints
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_sex_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_sex_check CHECK (sex IS NULL OR sex IN ('M', 'F'))");
        
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IS NULL OR role IN ('admin', 'finance', 'support'))");
    }
};
