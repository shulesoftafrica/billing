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
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PaymentGatewayTestController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('organization'),
                'bearer_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => null
            ]
        ]);
    });

    Route::post('register', function (Request $request) {
        try {
            $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,finance,support',
            'sex' => 'required|in:M,F',
            'device_name' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
            ], 422);
        }

        $user = User::create([
            'organization_id' => $request->organization_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sex' => $request->sex,
        ]);

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user->load('organization'),
                'bearer_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => null
            ]
        ], 201);
    });
    });


// Test route to verify authentication is working
Route::post('test-auth', function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated',
        'error' => 'Authentication required'
    ], 401);
})->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()->load('organization.currency')
    ]);
})->middleware('auth:sanctum');

// Logout routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    });

    Route::post('auth/generate-token', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string'
        ]);

        $user = $request->user();
        $tokenName = $validated['name'];
        $abilities = $validated['abilities'] ?? ['*']; // Default to all abilities
        
        // Create token
        $token = $user->createToken($tokenName, $abilities, $validated['expires_at'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Personal access token created successfully',
            'data' => [
                'personal_token' => $token->plainTextToken,
                'token_id' => $token->accessToken->id,
                'name' => $tokenName,
                'abilities' => $abilities,
                'expires_at' => $validated['expires_at'] ?? null,
                'created_at' => now()->toISOString()
            ]
        ], 201);
    });

    Route::get('auth/tokens', function (Request $request) {
        $tokens = $request->user()->tokens()->get(['id', 'name', 'abilities', 'created_at', 'last_used_at', 'expires_at']);
        
        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    });

    Route::delete('auth/tokens/{id}', function (Request $request, $tokenId) {
        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found'
            ], 404);
        }
        
        $token->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Token deleted successfully'
        ]);
    });

    Route::post('auth/logout-all', function (Request $request) {
        $request->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => 'All tokens have been revoked'
        ]);
    });
});

// Public API routes (No authentication required)
Route::get('currencies', [CurrencyController::class, 'index']);
Route::get('countries', [CountryController::class, 'index']);
Route::get('countries/{country}', [CountryController::class, 'show']);
Route::get('product-types', [ProductTypeController::class, 'index']);

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    // Countries (Admin only for CUD operations)
    Route::post('countries', [CountryController::class, 'store']);
    Route::put('countries/{country}', [CountryController::class, 'update']);
    Route::delete('countries/{country}', [CountryController::class, 'destroy']);
    
    // Currencies (Admin only for CUD operations)
    Route::post('currencies', [CurrencyController::class, 'store']);
    Route::put('currencies/{currency}', [CurrencyController::class, 'update']);
    Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy']);
    
    // All other resources require authentication
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    
    // Custom product routes
    Route::get('products/by-code/{product_code}', [ProductController::class, 'byCode']);
    
    // Note: GET product-types is public, only CREATE/UPDATE/DELETE require auth
    Route::post('product-types', [ProductTypeController::class, 'store']);
    Route::put('product-types/{product_type}', [ProductTypeController::class, 'update']);
    Route::delete('product-types/{product_type}', [ProductTypeController::class, 'destroy']);
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
    Route::get('subscriptions/{id}', [SubscriptionController::class, 'show']);
    Route::post('subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

    // Customer subscriptions routes
    Route::get('customers/{customer}/subscriptions', [SubscriptionController::class, 'getCustomerSubscriptions']);

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
        Route::get('by-phone/{phone}/status', [App\Http\Controllers\Api\CustomerController::class, 'lookupByPhoneWithStatus']);
        Route::get('by-email/{email}/status', [App\Http\Controllers\Api\CustomerController::class, 'lookupByEmailWithStatus']);
    });

    // Phase 2: Payment Gateway Testing - Protected
    Route::prefix('payment-gateways')->group(function () {
        Route::get('test-connection', [PaymentGatewayTestController::class, 'testConnection']);
        Route::get('test-all-connections', [PaymentGatewayTestController::class, 'testAllConnections']);
    });
});

// Public webhook routes (no authentication needed)
Route::prefix('webhooks')->group(function () {
    Route::post('unc-payment', [WebhookController::class, 'handleUNCPayment']);
    Route::post('stripe', [WebhookController::class, 'handleStripeWebhook']);
    Route::post('flutterwave', [WebhookController::class, 'handleFlutterWaveWebhook']);
    Route::post('test', [WebhookController::class, 'handleTestWebhook']);
});

// Payment endpoints
Route::get('payments/by-invoice/{invoice_id}', [\App\Http\Controllers\Api\PaymentController::class, 'getByInvoice']);
