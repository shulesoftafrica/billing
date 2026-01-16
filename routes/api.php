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
use App\Http\Controllers\Api\PricePlanController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes
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

// Webhook routes
Route::post('webhooks/unc-payment', [WebhookController::class, 'handleUNCPayment']);
