<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'organization_id' => 1,
                'product_type_id' => 2,
                'name' => 'Shulesoft System Fee',
                'description' => 'Fee to access and utilize Telegeram',
                'status' => 'active',
                'price_plans' => [
                    [
                        'name' => 'monthly Subscription',
                        'subscription_type' => 'monthly',
                        'amount' => 50000,
                        'currency' => 'TZS',
                    ],
                    [
                        'name' => 'Yearly Subscription',
                        'subscription_type' => 'yearly',
                        'amount' => 80000,
                        'currency' => 'TZS',
                    ],
                ],
            ],
        ];

        foreach ($products as $product) {
            $pricePlans = $product['price_plans'];
            unset($product['price_plans']);

            // Insert product
            $productId = DB::table('products')->insertGetId([
                'organization_id' => $product['organization_id'],
                'product_type_id' => $product['product_type_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'status' => $product['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert price plans for this product
            foreach ($pricePlans as $plan) {
                DB::table('price_plans')->insert([
                    'product_id' => $productId,
                    'name' => $plan['name'],
                    'subscription_type' => $plan['subscription_type'],
                    'amount' => $plan['amount'],
                    'currency' => $plan['currency'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
