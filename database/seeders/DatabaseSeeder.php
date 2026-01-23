<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed base tables first (countries, product types, payment gateways)
        $this->call([
            CountrySeeder::class,
            ProductTypeSeeder::class,
            PaymentGatewaySeeder::class,
        ]);

        // Seed organizations
        $this->call([
            OrganizationSeeder::class,
        ]);

        // Seed users (requires organizations to exist)
        $this->call([
            UserSeeder::class,
        ]);
    }
}
