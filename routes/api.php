<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
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

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('me', [AuthController::class, 'me']);
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
    });

    // Subscription routes
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

    // Customer subscriptions routes
    Route::get('customers/{customer}/subscriptions', [SubscriptionController::class, 'getCustomerSubscriptions']);

    // Product usage routes (Wallets)
    Route::post('product-usages', [ProductUsageController::class, 'store']);
    Route::get('product-usages/balance', [ProductUsageController::class, 'getBalance']);
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
| Legacy Routes - Deprecated (Backward Compatibility)
|--------------------------------------------------------------------------
| ⚠️ DEPRECATED: Use /api/v1/* routes instead
| These routes use the legacy APP_ACCESS_TOKEN authentication
| Will be removed in a future version
*/
Route::middleware(['app.access.token', 'throttle:30,1'])->group(function () {
    // Protected API routes
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
    Route::get('invoices/{id}/payment-gateways ', [InvoiceController::class, 'getPaymentGatewaysByInvoice']);
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
    });

    // Subscription routes
    Route::get('subscriptions', [SubscriptionController::class, 'index']);
    Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

    // Customer subscriptions routes
    Route::get('customers/{customer}/subscriptions', [SubscriptionController::class, 'getCustomerSubscriptions']);

    // Product usage routes
    Route::post('product-usages', [ProductUsageController::class, 'store']);
    Route::get('product-usages/balance', [ProductUsageController::class, 'getBalance']);
    Route::get('product-usages/{customer_id}/report', [ProductUsageController::class, 'getUsageReportByCustomer']);
    Route::get('product-usages/{customer_id}/{product_id}/history', [ProductUsageController::class, 'getHistory']);

    // Enhanced customer management
    Route::prefix('customers')->group(function () {
        Route::get('by-phone/{phone}', [App\Http\Controllers\Api\CustomerController::class, 'lookupByPhoneWithStatus']);
        Route::get('by-email/{email}', [App\Http\Controllers\Api\CustomerController::class, 'lookupByEmailWithStatus']);
    });

    // Payment endpoints
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
