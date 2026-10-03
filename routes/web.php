<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Placeholder sampai fitur reset kata sandi dibuat.
    Route::get('/forgot-password', ForgotPasswordController::class)->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Mengarahkan pengguna ke dashboard sesuai role-nya.
    Route::get('/dashboard', function () {
        $role = strtolower((string) auth()->user()->role);
        $target = config("perkoci.roles.{$role}.route");

        abort_unless($target, 403, 'Akun Anda belum memiliki peran yang valid.');

        return redirect()->route($target);
    })->name('dashboard');
});

// Dashboard per role (sementara masih halaman placeholder).
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'dashboard-placeholder')->name('dashboard');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::view('/dashboard', 'dashboard-placeholder')->name('dashboard');
});

Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::view('/dashboard', 'dashboard-placeholder')->name('dashboard');
});
