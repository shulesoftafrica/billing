<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $stripeSecret = (string) config('services.stripe.secret');

        if ($stripeSecret !== '') {
            Stripe::setApiKey($stripeSecret);
            
            // Warn if using test keys in production environment
            if (app()->environment('production') && str_starts_with($stripeSecret, 'sk_test_')) {
                Log::warning('Stripe is configured with TEST keys in PRODUCTION environment', [
                    'key_prefix' => substr($stripeSecret, 0, 8) . '...',
                    'environment' => app()->environment(),
                ]);
            }
            
            // Log the mode being used for debugging
            $mode = str_starts_with($stripeSecret, 'sk_live_') ? 'LIVE' : 'TEST';
            Log::info('Stripe API initialized', [
                'mode' => $mode,
                'environment' => app()->environment(),
            ]);
        }
    }
}
