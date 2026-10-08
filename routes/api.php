<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\AuthController;

Route::get('/health', [HealthController::class, 'check']);

// Auth (publik)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint terlindungi (token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Produk
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::put('/produk/{id}', [ProdukController::class, 'update']);
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

    // Meja
    Route::post('/meja', [MejaController::class, 'store']);
    Route::put('/meja/{id}', [MejaController::class, 'update']);
    Route::delete('/meja/{id}', [MejaController::class, 'destroy']);
});

// Baca data (publik, untuk self-order via QR)
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);
Route::get('/meja', [MejaController::class, 'index']);
Route::get('/meja/{id}', [MejaController::class, 'show']);
