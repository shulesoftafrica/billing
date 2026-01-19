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
        // Seed users first
        $this->call([
            UserSeeder::class,
        ]);

        // Seed base tables
        $this->call([
            CountrySeeder::class,
            CurrencySeeder::class,
            ProductTypeSeeder::class,
            PaymentGatewaySeeder::class,
        ]);
    }
}
