<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductTypeController;
use App\Http\Controllers\Api\ProductUsageController;
use App\Http\Controllers\Api\PricePlanController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PaymentGatewayTestController;

// Authentication routes - Public (no authentication, with strict rate limiting to prevent brute force)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
});

// Webhook routes - Public (no authentication, with strict rate limiting)
Route::middleware('throttle:30,1')->group(function () {
    Route::post('ecobank/notification', [WebhookController::class, 'handleUCNPayment']); //ucn
    Route::post('stripe', [WebhookController::class, 'handleStripeWebhook']); //stripe
    Route::post('flutterwave', [WebhookController::class, 'handleFlutterWaveWebhook']); //flutterwave
});

// Protected API routes - all require Sanctum authentication with rate limiting
// Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {

Route::middleware(['throttle:30,1'])->group(function () {
    // Auth routes
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected API routes
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('countries', CountryController::class);
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-types', ProductTypeController::class);
    Route::apiResource('payment-gateways', PaymentGatewayController::class);
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::apiResource('invoices', InvoiceController::class);

    // Organization payment gateway integration
    Route::post('organizations/integrate-payment-gateway', [OrganizationController::class, 'integratePaymentGateway']);

    // Customer addresses nested routes
    Route::prefix('customers/{customer}')->group(function () {
        Route::get('addresses', [CustomerAddressController::class, 'index']);
        Route::post('addresses', [CustomerAddressController::class, 'store']);
        Route::get('addresses/{address}', [CustomerAddressController::class, 'show']);
        Route::put('addresses/{address}', [CustomerAddressController::class, 'update']);
        Route::delete('addresses/{address}', [CustomerAddressController::class, 'destroy']);
    });

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
    Route::post('subscriptions', [SubscriptionController::class, 'store']);
    Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

    // Customer subscriptions routes
    Route::get('customers/{customer}/subscriptions', [SubscriptionController::class, 'getCustomerSubscriptions']);

    // Product usage routes
    Route::post('product-usages', [ProductUsageController::class, 'store']);
    Route::get('product-usages/balance', [ProductUsageController::class, 'getBalance']);
    Route::get('product-usages/{customer_id}/report', [ProductUsageController::class, 'getUsageReportByCustomer']);
    Route::get('product-usages/{customer_id}/{product_id}/history', [ProductUsageController::class, 'getHistory']);
});



// Wallet Management Routes (Phase 1 Implementation) - Protected
Route::prefix('wallets')->group(function () {
    Route::get('balance', [WalletController::class, 'getBalance']);
    Route::post('credit', [WalletController::class, 'addCredits']);
    Route::post('deduct', [WalletController::class, 'deductCredits']);
    Route::post('transfer', [WalletController::class, 'transferCredits']);
    Route::get('check-balance', [WalletController::class, 'checkBalance']);
    Route::get('{customer_id}/transactions', [WalletController::class, 'getTransactionHistory']);
});

// Phase 2: Advanced Invoice Types - Protected
Route::prefix('invoices')->group(function () {
    Route::post('wallet-topup', [App\Http\Controllers\Api\InvoiceController::class, 'createWalletTopupInvoice']);
    Route::post('plan-upgrade', [App\Http\Controllers\Api\InvoiceController::class, 'createPlanUpgradeInvoice']);
    Route::post('plan-downgrade', [App\Http\Controllers\Api\InvoiceController::class, 'createPlanDowngradeInvoice']);
});

// Phase 2: Enhanced Customer Management - Protected
Route::prefix('customers')->group(function () {
    Route::get('by-phone/{phone}/status', [App\Http\Controllers\Api\CustomerController::class, 'lookupByPhoneWithStatus']); //okay
    Route::get('by-email/{email}/status', [App\Http\Controllers\Api\CustomerController::class, 'lookupByEmailWithStatus']); //okay
});

// Phase 2: Payment Gateway Testing - Protected
Route::prefix('payment-gateways')->group(function () {
    Route::get('test-connection', [PaymentGatewayTestController::class, 'testConnection']);
    Route::get('test-all-connections', [PaymentGatewayTestController::class, 'testAllConnections']);
});

// Public webhook routes (no authentication needed)
Route::prefix('webhooks')->group(function () {
    Route::post('unc-payment', [WebhookController::class, 'handleUNCPayment']);

    Route::post('test', [WebhookController::class, 'handleTestWebhook']);
});

// Payment endpoints
Route::get('payments/by-invoice/{invoice_id}', [PaymentController::class, 'getByInvoice']);
Route::get('payments', [PaymentController::class, 'getByDateRange']);
Route::get('invoices/{product_id}/product', [InvoiceController::class, 'getByProduct']);
Route::post('invoices/by-subscriptions', [InvoiceController::class, 'getBySubscriptions']);
Route::get('wallets/transactions', [WalletController::class, 'getTransactionsByWallet']);
