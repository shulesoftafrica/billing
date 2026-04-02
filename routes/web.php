<?php

use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\ApiDocumentationController;
use App\Http\Controllers\OrganizationRegistrationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Dashboard\DashboardAuthController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Dashboard\ApiLogsController;
use App\Http\Controllers\Dashboard\OrganizationController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Dashboard Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/dashboard/login', [DashboardAuthController::class, 'showLogin'])->name('dashboard.login');
    Route::post('/dashboard/login', [DashboardAuthController::class, 'login'])->name('dashboard.login.post');
    Route::get('/dashboard/register', [DashboardAuthController::class, 'showRegister'])->name('dashboard.register');
    Route::post('/dashboard/register', [DashboardAuthController::class, 'register'])->name('dashboard.register.post');
});

Route::get('/dashboard/developer-verify/{user}', [DashboardAuthController::class, 'verifyDeveloper'])
    ->middleware('signed')
    ->name('dashboard.developer.verify');

// Dashboard (authenticated web session)
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [OverviewController::class, 'index'])->name('overview');
    Route::get('/api-logs', [ApiLogsController::class, 'index'])->name('api-logs');
    Route::get('/organization', [OrganizationController::class, 'index'])->name('organization');
    Route::put('/organization', [OrganizationController::class, 'update'])->name('organization.update');
    Route::post('/organization', [OrganizationController::class, 'store'])->name('organization.store');
    Route::post('/organization/credentials', [OrganizationController::class, 'generateCredentials'])->name('organization.credentials');
    Route::post('/logout', [DashboardAuthController::class, 'logout'])->name('logout');
});

// Named "login" route - used by Laravel auth middleware redirect
Route::get('/login', function () {
    if (request()->expectsJson() || request()->is('api/*')) {
        return response()->json([
            'message' => 'Unauthenticated',
            'error' => 'authentication_required',
            'hint' => 'This is an API endpoint. Please include Authorization header with Bearer token'
        ], 401);
    }
    return redirect()->route('dashboard.login');
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
