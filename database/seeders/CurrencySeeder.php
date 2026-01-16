<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'Tanzanian Shilling', 'code' => 'TZS', 'symbol' => 'TSh'],
            ['name' => 'Kenyan Shilling', 'code' => 'KES', 'symbol' => 'KSh'],
            ['name' => 'Ugandan Shilling', 'code' => 'UGX', 'symbol' => 'USh'],
            ['name' => 'Rwandan Franc', 'code' => 'RWF', 'symbol' => 'FRw'],
            ['name' => 'Burundian Franc', 'code' => 'BIF', 'symbol' => 'FBu'],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£'],
            ['name' => 'South African Rand', 'code' => 'ZAR', 'symbol' => 'R'],
            ['name' => 'Nigerian Naira', 'code' => 'NGN', 'symbol' => '₦'],
        ];

        foreach ($currencies as $currency) {
            DB::table('currencies')->insert([
                'name' => $currency['name'],
                'code' => $currency['code'],
                'symbol' => $currency['symbol'],
                'created_at' => now()
            ]);
        }
    }
}
