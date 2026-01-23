<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user for the first organization
        User::create([
            'organization_id' => 1,
            'name' => 'Admin User',
            'phone' => '+255654321000',
            'email' => 'admin@techsoft-solutions.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'sex' => 'M',
            'email_verified_at' => now(),
        ]);

        // Create manager user for the second organization
        User::create([
            'organization_id' => 1,
            'name' => 'Manager User',
            'phone' => '+255787654000',
            'email' => 'manager@mobilepay-tz.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
            'sex' => 'F',
            'email_verified_at' => now(),
        ]);

        // Create staff user for the third organization
        User::create([
            'organization_id' => 1,
            'name' => 'Staff User',
            'phone' => '+255713456000',
            'email' => 'staff@ecommerce-plus.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'sex' => 'M',
            'email_verified_at' => now(),
        ]);
    }
}

