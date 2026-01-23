<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'Shulesoft Company Limited',
                'phone' => '+255654321098',
                'email' => 'info@shulesoft.com',
                'currency' => json_encode(['TZS']),
                'country_id' => 1, // Tanzania
                'status' => 'active',
            ]
        ];

        foreach ($organizations as $organization) {
            DB::table('organizations')->insert([
                'name' => $organization['name'],
                'phone' => $organization['phone'],
                'email' => $organization['email'],
                'currency' => $organization['currency'],
                'country_id' => $organization['country_id'],
                'status' => $organization['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
