<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\OrganizationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes
Route::apiResource('currencies', CurrencyController::class);
Route::apiResource('countries', CountryController::class);
Route::apiResource('organizations', OrganizationController::class);
