<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckPaymentGatewayConfig extends Command
{
    protected $signature = 'payment-gateway:check';
    protected $description = 'Check payment gateway configuration for production readiness';

    public function handle()
    {
        $this->info('=== Payment Gateway Configuration Check ===');
        $this->newLine();

        $hasIssues = false;
        $environment = app()->environment();
        
        $this->line("Environment: <fg=yellow>{$environment}</>");
        $this->newLine();

        // Check Flutterwave
        $this->info('--- Flutterwave Configuration ---');
        
        // Check database config first (highest priority)
        $flutterwaveGateway = \App\Models\PaymentGateway::whereRaw('LOWER(name) = ?', ['flutterwave'])->first();
        $dbConfig = null;
        if ($flutterwaveGateway) {
            $dbConfig = is_array($flutterwaveGateway->config) ? $flutterwaveGateway->config : 
                        (is_string($flutterwaveGateway->config) ? json_decode($flutterwaveGateway->config, true) : null);
            
            if (is_array($dbConfig)) {
                $dbBaseUrl = $dbConfig['base_url'] ?? $dbConfig['gateway'] ?? null;
                if ($dbBaseUrl && str_contains($dbBaseUrl, 'dev-flutterwave.com') && $environment === 'production') {
                    $this->error("✗ Flutterwave: Database config has DEVELOPMENT URL in PRODUCTION");
                    $this->line("  DB config base_url: {$dbBaseUrl}");
                    $this->line("  This overrides environment variables!");
                    $hasIssues = true;
                } elseif ($dbBaseUrl) {
                    $isDev = str_contains($dbBaseUrl, 'dev-flutterwave.com');
                    $this->line($isDev ? 
                        "  DB config base_url: <fg=yellow>{$dbBaseUrl}</> (DEVELOPMENT)" :
                        "  DB config base_url: <fg=green>{$dbBaseUrl}</> (PRODUCTION)"
                    );
                }
            }
        }
        
        $flutterwaveUrl = config('services.flutterwave.v3_base_url');
        $flutterwaveSecret = config('services.flutterwave.secret_key');
        
        if (!$flutterwaveSecret) {
            $this->warn('⚠ Flutterwave secret key not configured');
            $hasIssues = true;
        } else {
            $secretPrefix = substr($flutterwaveSecret, 0, 15);
            $isTest = stripos($flutterwaveSecret, 'test') !== false;
            
            if ($isTest && $environment === 'production') {
                $this->error('✗ Flutterwave: Using TEST keys in PRODUCTION environment');
                $this->line("  Key prefix: {$secretPrefix}...");
                $hasIssues = true;
            } else {
                $this->line($isTest ? 
                    "<fg=yellow>✓ Flutterwave: Using TEST keys (mode: {$environment})</>" : 
                    '<fg=green>✓ Flutterwave: Using LIVE keys</>'
                );
            }
        }

        if ($flutterwaveUrl) {
            if (str_contains($flutterwaveUrl, 'dev-flutterwave.com') && $environment === 'production') {
                $this->error("✗ Flutterwave: Using DEVELOPMENT URL in PRODUCTION");
                $this->line("  URL: {$flutterwaveUrl}");
                $hasIssues = true;
            } else {
                $isDev = str_contains($flutterwaveUrl, 'dev-flutterwave.com');
                $this->line($isDev ? 
                    "<fg=yellow>  URL: {$flutterwaveUrl} (DEVELOPMENT)</>" :
                    "<fg=green>  URL: {$flutterwaveUrl} (PRODUCTION)</>"
                );
            }
        } else {
            $this->line('  URL: <fg=green>https://api.flutterwave.com (default - PRODUCTION)</>');
        }

        $this->newLine();

        // Check Stripe
        $this->info('--- Stripe Configuration ---');
        
        // Check database config first
        $stripeGateway = \App\Models\PaymentGateway::whereRaw('LOWER(name) = ?', ['stripe'])->first();
        if ($stripeGateway) {
            $dbConfig = is_array($stripeGateway->config) ? $stripeGateway->config : 
                        (is_string($stripeGateway->config) ? json_decode($stripeGateway->config, true) : null);
            
            if (is_array($dbConfig) && isset($dbConfig['api_key'])) {
                $dbApiKey = $dbConfig['api_key'];
                $dbKeyPrefix = substr($dbApiKey, 0, 8);
                $isDbTest = str_starts_with($dbApiKey, 'sk_test_');
                
                if ($isDbTest && $environment === 'production') {
                    $this->error("✗ Stripe: Database config has TEST key in PRODUCTION");
                    $this->line("  DB config api_key: {$dbKeyPrefix}...");
                    $this->line("  This could override environment variables!");
                    $hasIssues = true;
                } else {
                    $this->line($isDbTest ?
                        "  DB config api_key: <fg=yellow>{$dbKeyPrefix}...</> (TEST)" :
                        "  DB config api_key: <fg=green>{$dbKeyPrefix}...</> (LIVE)"
                    );
                }
            }
        }
        
        $stripeSecret = config('services.stripe.secret');
        $stripePublishable = config('services.stripe.publishable_key');
        
        if (!$stripeSecret) {
            $this->warn('⚠ Stripe secret key not configured');
            $hasIssues = true;
        } else {
            $secretPrefix = substr($stripeSecret, 0, 8);
            $isTest = str_starts_with($stripeSecret, 'sk_test_');
            $isLive = str_starts_with($stripeSecret, 'sk_live_');
            
            if ($isTest && $environment === 'production') {
                $this->error('✗ Stripe: Using TEST keys in PRODUCTION environment');
                $this->line("  Secret key: {$secretPrefix}...");
                $hasIssues = true;
            } elseif ($isLive) {
                $this->line("<fg=green>✓ Stripe: Using LIVE keys</>");
                $this->line("  Secret key: {$secretPrefix}...");
            } elseif ($isTest) {
                $this->line("<fg=yellow>✓ Stripe: Using TEST keys (mode: {$environment})</>");
                $this->line("  Secret key: {$secretPrefix}...");
            } else {
                $this->error('✗ Stripe: Invalid key format');
                $this->line("  Secret key: {$secretPrefix}...");
                $hasIssues = true;
            }
        }

        if ($stripePublishable) {
            $publishablePrefix = substr($stripePublishable, 0, 8);
            $isTest = str_starts_with($stripePublishable, 'pk_test_');
            $isLive = str_starts_with($stripePublishable, 'pk_live_');
            
            if ($isTest && $environment === 'production') {
                $this->error('✗ Stripe: Using TEST publishable key in PRODUCTION');
                $this->line("  Publishable key: {$publishablePrefix}...");
                $hasIssues = true;
            } elseif ($isLive) {
                $this->line("  Publishable key: {$publishablePrefix}... (LIVE)");
            } elseif ($isTest) {
                $this->line("  Publishable key: {$publishablePrefix}... (TEST)");
            }
        }

        $stripeWebhook = config('services.stripe.webhook_secret');
        if (!$stripeWebhook) {
            $this->warn('⚠ Stripe webhook secret not configured (optional but recommended)');
        } else {
            $webhookPrefix = substr($stripeWebhook, 0, 12);
            $this->line("  Webhook secret: {$webhookPrefix}...");
        }

        $this->newLine();
        $this->info('=== Summary ===');
        
        if ($hasIssues) {
            $this->error('✗ Configuration issues detected!');
            $this->newLine();
            $this->line('Actions required:');
            $this->line('1. Update your .env file with production/live credentials');
            $this->line('2. Run: php artisan config:clear');
            $this->line('3. Restart your application');
            $this->line('4. Run this check again to verify');
            $this->newLine();
            $this->line('See PAYMENT_GATEWAY_PRODUCTION_SETUP.md for detailed instructions.');
            return 1;
        }

        $this->line('<fg=green>✓ All payment gateway configurations look good!</>');
        
        if ($environment !== 'production') {
            $this->newLine();
            $this->comment('Note: Not running in production environment.');
            $this->comment('Make sure to use LIVE keys when deploying to production.');
        }

        return 0;
    }
}
