<?php
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\HistoryController as KasirHistoryController;
use App\Http\Controllers\Kasir\OrderController as KasirOrderController;
use App\Http\Controllers\Kasir\PaymentController as KasirPaymentController;
use App\Http\Controllers\Kasir\SettingsController as KasirSettingsController;
use App\Http\Controllers\Customer\CartController as OrderCartController;
use App\Http\Controllers\Customer\CheckoutController as OrderCheckoutController;
use App\Http\Controllers\Customer\MenuController as OrderMenuController;
use App\Http\Controllers\Customer\WelcomeController as OrderWelcomeController;
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
    Route::get('/dashboard', KasirDashboardController::class)->name('dashboard');
    Route::get('/orders', [KasirOrderController::class, 'index'])->name('orders.index');
    Route::get('/payment/{code}', [KasirPaymentController::class, 'show'])->name('payment.show');
    Route::get('/history', [KasirHistoryController::class, 'index'])->name('history.index');
    Route::get('/settings', [KasirSettingsController::class, 'index'])->name('settings.index');
});

// Self-order customer (publik, tanpa login — konteks meja via QR ?meja= / ?qr=, cart di session).
Route::prefix('order')->name('order.')->group(function () {
    Route::get('/', [OrderWelcomeController::class, 'welcome'])->name('welcome');
    Route::post('/waiter', [OrderWelcomeController::class, 'waiter'])->name('waiter');
    Route::get('/menu', [OrderMenuController::class, 'menu'])->name('menu');
    Route::get('/menu/{id}', [OrderMenuController::class, 'product'])->name('product');
    Route::get('/cart', [OrderCartController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [OrderCartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [OrderCartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [OrderCartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [OrderCheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [OrderCheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/status', [OrderCheckoutController::class, 'status'])->name('status');
    Route::get('/receipt', [OrderCheckoutController::class, 'receipt'])->name('receipt');
});
