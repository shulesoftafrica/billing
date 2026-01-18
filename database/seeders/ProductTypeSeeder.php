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
            [
                'id' => 1,
                'name' => 'One-time Product'
            ],
            [
                'id' => 2, 
                'name' => 'Subscription Product'
            ],
        ];

        foreach ($productTypes as $type) {
            DB::table('product_types')->updateOrInsert(
                ['id' => $type['id']],
                [
                    'name' => $type['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
