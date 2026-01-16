<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Tanzania', 'code' => 'TZA'],
            ['name' => 'Kenya', 'code' => 'KEN'],
            ['name' => 'Uganda', 'code' => 'UGA'],
            ['name' => 'Rwanda', 'code' => 'RWA'],
            ['name' => 'Burundi', 'code' => 'BDI'],
            ['name' => 'South Africa', 'code' => 'ZAF'],
            ['name' => 'Nigeria', 'code' => 'NGA'],
            ['name' => 'Ghana', 'code' => 'GHA'],
            ['name' => 'Egypt', 'code' => 'EGY'],
            ['name' => 'Morocco', 'code' => 'MAR'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->insert([
                'name' => $country['name'],
                'code' => $country['code'],
                'created_at' => now()
            ]);
        }
    }
}
