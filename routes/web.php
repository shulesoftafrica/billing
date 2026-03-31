<?php

use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\ApiDocumentationController;
use App\Http\Controllers\OrganizationRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('api.docs');
});

// Fallback login route for API authentication errors
Route::get('/login', function () {
    if (request()->expectsJson() || request()->is('api/*')) {
        return response()->json([
            'message' => 'Unauthenticated',
            'error' => 'authentication_required',
            'hint' => 'This is an API endpoint. Please include Authorization header with Bearer token'
        ], 401);
    }
    
    return response()->json([
        'message' => 'Login page not implemented',
        'hint' => 'Use POST /api/v1/auth/login for authentication'
    ], 404);
})->name('login');

Route::get('/billing/pay/{invoice}', [PaymentPageController::class, 'show'])
    ->name('billing.payment.show');

Route::get('/billing/pay/{invoice}/complete', [PaymentPageController::class, 'complete'])
    ->name('billing.payment.complete');

Route::get('/api-docs', [ApiDocumentationController::class, 'index'])->name('api.docs');

// Organization Registration
Route::get('/organizations/register', [OrganizationRegistrationController::class, 'create'])
    ->name('organizations.register');
Route::post('/organizations/register', [OrganizationRegistrationController::class, 'store'])
    ->name('organizations.register.store');
Route::get('/organizations/register/success', [OrganizationRegistrationController::class, 'success'])
    ->name('organizations.register.success');
