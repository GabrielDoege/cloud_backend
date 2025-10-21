<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/validate', [AuthController::class, 'validateToken']);
Route::get('/me', [AuthController::class, 'me']);
