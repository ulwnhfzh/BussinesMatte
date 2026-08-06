<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AICopilotController;
use App\Http\Controllers\POSCashierController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. HALAMAN UTAMA
// ==========================================
Route::get('/', function () {
    return redirect('/login');
});

// ==========================================
// 2. ROUTE GUEST
// ==========================================
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    // Register
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'register']);

    // Lupa Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

// ==========================================
// 3. ROUTE USER YANG SUDAH LOGIN
// ==========================================
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // ================================
    // A. DASHBOARD
    // ================================
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
    // ================================
// B. MANAJEMEN INVENTARIS
// ================================

// Halaman utama dan penyimpanan produk.
Route::get(
    '/inventory',
    [ProductController::class, 'index']
)->name('inventory');

Route::post(
    '/inventory',
    [ProductController::class, 'store']
)->name('inventory.store');

// Route statis harus berada sebelum route /inventory/{id}.
Route::get(
    '/inventory/prediction',
    [ProductController::class, 'getPrediction']
)->name('inventory.prediction');

Route::get(
    '/inventory/generate-code',
    [ProductController::class, 'generateCodeAjax']
)->name('inventory.generate');

Route::get(
    '/inventory/stock-movements',
    [StockMovementController::class, 'index']
)->name('inventory.stock-movements');

// Route dengan parameter ID produk.
Route::get(
    '/inventory/{id}/edit',
    [ProductController::class, 'edit']
)
    ->whereNumber('id')
    ->name('inventory.edit');

Route::get(
    '/inventory/{id}',
    [ProductController::class, 'show']
)
    ->whereNumber('id')
    ->name('inventory.detail');

Route::put(
    '/inventory/{id}',
    [ProductController::class, 'update']
)
    ->whereNumber('id')
    ->name('inventory.update');

Route::delete(
    '/inventory/{id}',
    [ProductController::class, 'destroy']
)
    ->whereNumber('id')
    ->name('inventory.destroy');

    // ================================
    // C. ANALYTICS
    // ================================
    Route::get('/analytics', [AnalyticsController::class, 'index'])
        ->name('analytics');

    Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData'])
        ->name('analytics.chart');

    // ================================
    // D. AI COPILOT
    // ================================
    Route::get('/ai-copilot', [AICopilotController::class, 'index'])
    ->name('ai.copilot');

Route::post('/ai-copilot/chat', [AICopilotController::class, 'chat'])
    ->name('ai.copilot.chat');

    // ================================
    // E. POS CASHIER
    // ================================
    Route::get('/pos-cashier', [POSCashierController::class, 'index'])
        ->name('pos.cashier');

    Route::post('/pos/add', [POSCashierController::class, 'addToCart'])
        ->name('pos.add');

    Route::post('/pos/update', [POSCashierController::class, 'updateCart'])
        ->name('pos.update');

    Route::delete('/pos/remove/{id}', [POSCashierController::class, 'removeFromCart'])
        ->whereNumber('id')
        ->name('pos.remove');

    Route::post('/pos/clear', [POSCashierController::class, 'clearCart'])
        ->name('pos.clear');

    Route::post('/pos/checkout', [POSCashierController::class, 'checkout'])
        ->name('pos.checkout');

    Route::post('/pos/save', [POSCashierController::class, 'checkout'])
        ->name('pos.save');
    // --- PRIORITAS 2 & 3: RIWAYAT, DETAIL & CETAK STRUK ---
    Route::get('/pos/transactions', [POSCashierController::class, 'transactionsHistory'])
        ->name('pos.transactions.index');

    Route::get('/pos/transactions/{id}', [POSCashierController::class, 'transactionDetail'])
        ->whereNumber('id')
        ->name('pos.transactions.show');

Route::get('/pos/transactions/{id}/print', [POSCashierController::class, 'printReceipt'])
        ->whereNumber('id')
        ->name('pos.transactions.print');

    // --- PRIORITAS 8: VOID, REFUND & RETUR ---
    Route::post('/pos/transactions/{id}/void', [POSCashierController::class, 'voidTransaction'])
        ->whereNumber('id')
        ->name('pos.transactions.void');

    Route::post('/pos/transactions/{id}/refund', [POSCashierController::class, 'refundTransaction'])
        ->whereNumber('id')
        ->name('pos.transactions.refund');

    Route::post('/pos/transactions/{id}/return', [POSCashierController::class, 'returnTransaction'])
        ->whereNumber('id')
        ->name('pos.transactions.return');
    // Tambahkan route ini di routes/web.php
    Route::get('/pos/history', [POSCashierController::class, 'transactionsHistory'])->name('pos.history');
    // ================================
    // F. PENGATURAN
    // ================================
    Route::get('/pengaturan', [PengaturanController::class, 'index'])
        ->name('pengaturan');

    Route::put('/pengaturan/update', [PengaturanController::class, 'updateProfile'])
        ->name('pengaturan.update');

    Route::put('/pengaturan/password', [PengaturanController::class, 'updatePassword'])
        ->name('pengaturan.password');

    Route::post('/pengaturan/export-laporan', [ReportExportController::class, 'export'])->name('pengaturan.reports.export');
});