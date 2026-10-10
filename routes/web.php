<?php
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\TableController as AdminTableController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\FinanceController as ManagerFinanceController;
use App\Http\Controllers\Manager\Pb1Controller as ManagerPb1Controller;
use App\Http\Controllers\Manager\SalesController as ManagerSalesController;
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
use App\Http\Controllers\Auth\KasirLoginController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Placeholder sampai fitur reset kata sandi dibuat.
    Route::get('/forgot-password', ForgotPasswordController::class)->name('password.request');

    // Terminal login Kasir (PIN + shift).
    Route::get('/kasir/login', [KasirLoginController::class, 'create'])->name('kasir.login');
    Route::post('/kasir/login', [KasirLoginController::class, 'store'])->name('kasir.login.store');
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

// Panel admin (menggantikan placeholder).
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::get('/menu', [AdminMenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/create', [AdminMenuController::class, 'create'])->name('menu.create');
    Route::post('/menu', [AdminMenuController::class, 'store'])->name('menu.store');
    Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('menu.edit');
    Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('menu.update');
    Route::patch('/menu/{id}/toggle', [AdminMenuController::class, 'toggle'])->name('menu.toggle');
    Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('/stocks', [AdminStockController::class, 'index'])->name('stocks.index');
    Route::post('/stocks/restock', [AdminStockController::class, 'restock'])->name('stocks.restock');

    Route::get('/tables', [AdminTableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [AdminTableController::class, 'store'])->name('tables.store');
    Route::patch('/tables/{id}', [AdminTableController::class, 'updateStatus'])->name('tables.status');
    Route::post('/tables/{id}/regenerate', [AdminTableController::class, 'regenerate'])->name('tables.regenerate');
    Route::delete('/tables/{id}', [AdminTableController::class, 'destroy'])->name('tables.destroy');

    Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit.index');
});

Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', ManagerDashboardController::class)->name('dashboard');
    Route::get('/penjualan', [ManagerSalesController::class, 'index'])->name('sales.index');
    Route::get('/penjualan/export', [ManagerSalesController::class, 'export'])->name('sales.export');
    Route::get('/keuangan', [ManagerFinanceController::class, 'index'])->name('finance.index');
    Route::post('/keuangan/keluar', [ManagerFinanceController::class, 'storeKeluar'])->name('finance.keluar.store');
    Route::delete('/keuangan/keluar/{id}', [ManagerFinanceController::class, 'destroyKeluar'])->name('finance.keluar.destroy');
    Route::get('/keuangan/export', [ManagerFinanceController::class, 'export'])->name('finance.export');
    Route::get('/pb1', [ManagerPb1Controller::class, 'index'])->name('pb1.index');
    Route::get('/promo', [ManagerSalesController::class, 'promo'])->name('promo.index');
    Route::get('/promo/export', [ManagerSalesController::class, 'exportPromo'])->name('promo.export');
    Route::get('/export', [ManagerSalesController::class, 'center'])->name('export.index');
});

Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', KasirDashboardController::class)->name('dashboard');
    Route::get('/orders', [KasirOrderController::class, 'index'])->name('orders.index');
    Route::get('/payment/{code}', [KasirPaymentController::class, 'show'])->name('payment.show');
    Route::get('/history', [KasirHistoryController::class, 'index'])->name('history.index');
    Route::get('/settings', [KasirSettingsController::class, 'index'])->name('settings.index');
    Route::post('/logout', [KasirLoginController::class, 'destroy'])->name('logout');
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
