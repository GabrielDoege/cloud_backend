<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RentalController;

Route::apiResource('locations', LocationController::class);
Route::post('rentals', [RentalController::class, 'store']);
Route::get('rentals/history', [RentalController::class, 'history']);
Route::match(['put', 'patch'], 'rentals/{id}', [RentalController::class, 'update']);
Route::post('rentals/calculate-price', [RentalController::class, 'calculatePrice']);
