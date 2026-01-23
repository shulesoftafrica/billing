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
            ['name' => 'Tanzania', 'code' => 'TZ'],
            ['name' => 'Kenya', 'code' => 'KE'],
            ['name' => 'Uganda', 'code' => 'UG'],
            ['name' => 'Rwanda', 'code' => 'RW'],
            ['name' => 'Burundi', 'code' => 'BI'],
            ['name' => 'South Africa', 'code' => 'ZA'],
            ['name' => 'Nigeria', 'code' => 'NG'],
            ['name' => 'Ghana', 'code' => 'GH'],
            ['name' => 'Egypt', 'code' => 'EG'],
            ['name' => 'Morocco', 'code' => 'MA'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->insert([
                'name' => $country['name'],
                'code' => $country['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
