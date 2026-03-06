<?php

use App\Http\Controllers\PaymentPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/billing/pay/{invoice}', [PaymentPageController::class, 'show'])
    ->name('billing.payment.show');

Route::get('/billing/pay/{invoice}/complete', [PaymentPageController::class, 'complete'])
    ->name('billing.payment.complete');
