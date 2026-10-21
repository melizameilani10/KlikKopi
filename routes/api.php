<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProdukApiController;
use App\Http\Controllers\Api\MejaApiController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\Kasir\PesananController as KasirPesananController;

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

/*
|--------------------------------------------------------------------------
| API v1 — Alur transaksi KlikKopi
|--------------------------------------------------------------------------
| Response seragam { success, message, data }.
| Route publik sebelum auth, sisanya dilindungi auth:sanctum + role.
*/
Route::prefix('v1')->group(function () {
    // Publik: katalog produk + ketersediaan meja.
    Route::get('/produk', [ProdukApiController::class, 'index']);
    Route::get('/meja', [MejaApiController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        // Customer: buat pesanan & bayar.
        Route::middleware('role:customer')->group(function () {
            Route::post('/pesanan', [PesananController::class, 'store']);
            Route::post('/pesanan/{kode}/bayar', [PesananController::class, 'bayar']);
        });

        // Kasir: antrean & verifikasi.
        Route::middleware('role:kasir')->prefix('kasir')->group(function () {
            Route::get('/pesanan/pending', [KasirPesananController::class, 'pending']);
            Route::put('/pesanan/{kode}/verifikasi', [KasirPesananController::class, 'verifikasi']);
        });
    });
});
