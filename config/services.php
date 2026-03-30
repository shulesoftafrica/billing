<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'flutterwave' => [
        'encryption_key' => env('ENCRYPTION_KEY'),
        'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY', env('FLW_SECRET_KEY')),
        // Production URL: https://api.flutterwave.com
        // Development URL: https://api-dev.flutterwave.com (NOT RECOMMENDED for production)
        // Defaults to production if not set
        'v3_base_url' => env('FLUTTERWAVE_V3_BASE_URL', 'https://api.flutterwave.com'),
        'api_key' => env('FLUTTERWAVE_API_KEY'),
    ],

    'stripe' => [
        // Use sk_live_* for production, sk_test_* for development
        'secret' => env('STRIPE_SECRET_KEY'),
        // Use pk_live_* for production, pk_test_* for development
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
