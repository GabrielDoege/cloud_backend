<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;

Route::apiResource('locations', LocationController::class);

Route::post('rentals', [RentalController::class, 'store']);
Route::get('rentals/history', [RentalController::class, 'history']);
Route::match(['put', 'patch'], 'rentals/{id}', [RentalController::class, 'update']);
Route::post('rentals/calculate-price', [RentalController::class, 'calculatePrice']);

Route::post('reviews', [ReviewController::class, 'store']);
Route::get('reviews', [ReviewController::class, 'index']);