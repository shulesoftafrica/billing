<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            ['name' => 'Wallet Based Product'],
            ['name' => 'Subscription Based Product'],
        ];

        foreach ($productTypes as $type) {

            DB::table('product_types')->insert([
                'name' => $type['name'],
                'created_at' => now()
            ]);
        }
    }
}
