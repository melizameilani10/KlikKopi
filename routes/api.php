<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\MejaController;
use App\Http\Controllers\AuthController;

Route::get('/health', [HealthController::class, 'check']);

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Produk
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);
Route::post('/produk', [ProdukController::class, 'store']);
Route::put('/produk/{id}', [ProdukController::class, 'update']);
Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

// Meja
Route::get('/meja', [MejaController::class, 'index']);
Route::get('/meja/{id}', [MejaController::class, 'show']);
Route::post('/meja', [MejaController::class, 'store']);
Route::put('/meja/{id}', [MejaController::class, 'update']);
Route::delete('/meja/{id}', [MejaController::class, 'destroy']);
