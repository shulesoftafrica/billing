<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

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
        // Use public build path or fallback gracefully
        Vite::useBuildDirectory('build');
        
        // Prevent Vite errors in production if manifest is missing
        if (app()->environment('production') && !file_exists(public_path('build/manifest.json'))) {
            config(['app.asset_url' => config('app.url')]);
        }
    }
}
