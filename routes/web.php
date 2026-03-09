<?php

use App\Http\Controllers\PaymentPageController;
use App\Http\Controllers\ApiDocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/billing/pay/{invoice}', [PaymentPageController::class, 'show'])
    ->name('billing.payment.show');

Route::get('/billing/pay/{invoice}/complete', [PaymentPageController::class, 'complete'])
    ->name('billing.payment.complete');

Route::get('/api-docs', [ApiDocumentationController::class, 'index'])->name('api.docs');
