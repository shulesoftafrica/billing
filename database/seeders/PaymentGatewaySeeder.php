<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentGateways = [
            [
                'name' => 'Universal Control Number',
                'type' => 'control_number',
                'webhook_secret' => Str::random(64),
                'config' => json_encode([
                    'api_key' => Str::random(32),
                    'webhook_url' => '/api/webhooks/ucn',
                ]),
                'active' => true,
            ],
            [
                'name' => 'Stripe',
                'type' => 'card',
                'webhook_secret' => Str::random(64),
                'config' => json_encode([
                    'api_key' => 'sk_test_' . Str::random(24),
                    'public_key' => 'pk_test_' . Str::random(24),
                    'webhook_url' => '/api/webhooks/stripe',
                ]),
                'active' => true,
            ],
            [
                'name' => 'PayPal',
                'type' => 'card',
                'webhook_secret' => Str::random(64),
                'config' => json_encode([
                    'client_id' => Str::random(32),
                    'client_secret' => Str::random(32),
                    'mode' => 'sandbox',
                ]),
                'active' => true,
            ],
            [
                'name' => 'Flutterwave',
                'type' => 'card',
                'webhook_secret' => Str::random(64),
                'config' => json_encode([
                    'public_key' => 'FLWPUBK_TEST-' . Str::random(32),
                    'secret_key' => 'FLWSECK_TEST-' . Str::random(32),
                    'encryption_key' => 'FLWSECK_TEST' . Str::random(12),
                    'webhook_url' => '/api/webhooks/flutterwave',
                ]),
                'active' => true,
            ],
        ];

        foreach ($paymentGateways as $gateway) {
            DB::table('payment_gateways')->insert([
                'name' => $gateway['name'],
                'type' => $gateway['type'],
                'webhook_secret' => $gateway['webhook_secret'],
                'config' => $gateway['config'],
                'active' => $gateway['active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
