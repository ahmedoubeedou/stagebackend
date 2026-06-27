<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── PUBLIC ROUTES (no token needed) ─────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/cars',      [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);

// ── PRIVATE ROUTES (Sanctum token required) ─────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',           [AuthController::class, 'logout']);
    Route::get('/user',              [AuthController::class, 'me']);
    
    // User Cars Management
    Route::get('/user/cars',         [CarController::class, 'myCars']);
    Route::post('/cars',             [CarController::class, 'store']);
    Route::put('/cars/{id}',         [CarController::class, 'update']);
    Route::delete('/cars/{id}',      [CarController::class, 'destroy']);
    
    // Favorites Management
    Route::get('/user/favorites',            [FavoriteController::class, 'index']);
    Route::post('/user/favorites/{carId}',   [FavoriteController::class, 'store']);
    Route::delete('/user/favorites/{carId}', [FavoriteController::class, 'destroy']);
    Route::get('/user/favorites/{carId}',    [FavoriteController::class, 'show']);
});