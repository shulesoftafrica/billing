<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ClientCredentialsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductTypeController;
use App\Http\Controllers\Api\ProductUsageController;
use App\Http\Controllers\Api\PricePlanController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Api\CustomWebhookController;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
| User authentication (email/password) - for web and mobile apps
*/
Route::prefix('v1/auth')->group(function () {
    // User authentication (email/password)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('generate-token', [AuthController::class, 'generateToken']);
        Route::get('tokens', [AuthController::class, 'listTokens']);
        Route::delete('tokens/{id}', [AuthController::class, 'revokeToken']);
    });
});

/*
|--------------------------------------------------------------------------
| OAuth Client Credentials Routes
|--------------------------------------------------------------------------
| OAuth2 client credentials grant (client_id/client_secret) - for API integrations
*/
Route::prefix('v1/oauth')->group(function () {
    // Public: Get access token using client credentials
    Route::post('token', [ClientCredentialsController::class, 'getToken']);
    
    // Protected: Manage OAuth clients (requires user authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('clients', [ClientCredentialsController::class, 'createClient']);
        Route::get('clients', [ClientCredentialsController::class, 'listClients']);
        Route::delete('clients/{id}', [ClientCredentialsController::class, 'revokeClient']);
    });
});

/*
|--------------------------------------------------------------------------
| API v1 Routes - Organization-scoped Sanctum Authentication
|--------------------------------------------------------------------------
| All routes in this group require:
| - Sanctum authentication (auth:sanctum)
| - Organization scope validation (organization.scope)
| - Rate limiting (throttle:60,1)
*/
Route::middleware(['auth:sanctum', 'organization.scope', 'throttle:60,1'])->prefix('v1')->group(function () {
    // Core resource routes
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-types', ProductTypeController::class);
    Route::apiResource('payment-gateways', PaymentGatewayController::class);
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::apiResource('invoices', InvoiceController::class)->except(['update', 'destroy']);
    Route::get('invoices/{product_id}/product', [InvoiceController::class, 'getByProduct']);
    Route::post('invoices/by-subscriptions', [InvoiceController::class, 'getBySubscriptions']);
    Route::get('invoices/{id}/payment-gateways', [InvoiceController::class, 'getPaymentGatewaysByInvoice']);
    Route::post('invoices/{id}/cancel', [InvoiceController::class, 'cancel']);
    Route::post('invoices/plan-upgrade', [InvoiceController::class, 'upgradeSubscription']);
    Route::post('invoices/plan-downgrade', [InvoiceController::class, 'downgradeSubscription']);
    Route::apiResource('tax-rates', TaxRateController::class);

    // Organization payment gateway integration
    Route::post('organizations/integrate-payment-gateway', [OrganizationController::class, 'integratePaymentGateway']);

    // Product price plans nested routes
    Route::prefix('products/{product}')->group(function () {
        Route::get('price-plans', [PricePlanController::class, 'index']);
        Route::post('price-plans', [PricePlanController::class, 'store']);
        Route::get('price-plans/{pricePlan}', [PricePlanController::class, 'show']);
        Route::put('price-plans/{pricePlan}', [PricePlanController::class, 'update']);
        Route::delete('price-plans/{pricePlan}', [PricePlanController::class, 'destroy']);
        
        // Custom webhooks nested under products
        Route::get('webhooks', [CustomWebhookController::class, 'index']);
        Route::post('webhooks', [CustomWebhookController::class, 'store']);
        Route::get('webhooks/{webhook}', [CustomWebhookController::class, 'show']);
        Route::put('webhooks/{webhook}', [CustomWebhookController::class, 'update']);
        Route::delete('webhooks/{webhook}', [CustomWebhookController::class, 'destroy']);
        Route::post('webhooks/{webhook}/test', [CustomWebhookController::class, 'test']);
        Route::get('webhooks/{webhook}/deliveries', [CustomWebhookController::class, 'deliveries']);
        Route::post('webhooks/{webhook}/regenerate-secret', [CustomWebhookController::class, 'regenerateSecret']);
        Route::post('webhooks/{webhook}/replay', [CustomWebhookController::class, 'replay']);
    });

    // Subscription routes
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::post('subscriptions', [SubscriptionController::class, 'store']);
    Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

    // Customer subscriptions routes
    Route::get('customers/{customer}/subscriptions', [SubscriptionController::class, 'getCustomerSubscriptions']);

    // Wallets routes
    Route::get('wallets', [ProductController::class, 'wallets']);

    // Product usage routes (Wallets)
    Route::post('product-usages', [ProductUsageController::class, 'store']);
    Route::get('product-usages/{wallet_id}/balance', [ProductUsageController::class, 'getBalance']);
    Route::get('product-usages/{customer_id}/report', [ProductUsageController::class, 'getUsageReportByCustomer']);
    Route::get('product-usages/{customer_id}/{product_id}/history', [ProductUsageController::class, 'getHistory']);

    // Enhanced customer management
    Route::prefix('customers')->group(function () {
        Route::get('by-phone/{phone}', [CustomerController::class, 'lookupByPhoneWithStatus']);
        Route::get('by-email/{email}', [CustomerController::class, 'lookupByEmailWithStatus']);
    });

    // Payment endpoints (Reconciliation)
    Route::get('payments/by-invoice/{invoice_id}', [PaymentController::class, 'getByInvoice']);
    Route::get('payments', [PaymentController::class, 'getByDateRange']);
});

/*
|--------------------------------------------------------------------------
| Public Webhook Routes
|--------------------------------------------------------------------------
| No authentication required - Webhooks validate via signature verification
*/
Route::prefix('v1')->group(function () {
    Route::middleware('throttle:30,1')->group(function () {
        Route::prefix('webhooks')->group(function () {
            Route::post('ecobank/notification', [WebhookController::class, 'handleUCNPayment']); //ucn
            Route::post('flutterwave', [WebhookController::class, 'handleFlutterWaveWebhook']); //flutterwave
            Route::post('stripe', StripeWebhookController::class); //stripe
        });
    });
});
