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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. HALAMAN UTAMA (REDIRECT)
// ==========================================
Route::get('/', function () {
    return redirect('/login');
});

// ==========================================
// 2. ROUTE UNTUK GUEST (BELUM LOGIN)
// ==========================================
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Lupa Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ==========================================
// 3. ROUTE UNTUK USER YANG SUDAH LOGIN (AUTH)
// ==========================================
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ================================
    // A. DASHBOARD
    // ================================
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ================================
    // B. MANAJEMEN INVENTARIS
    // ================================
    Route::get('/inventory', [ProductController::class, 'index'])->name('inventory');
    Route::post('/inventory', [ProductController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{id}/edit', [ProductController::class, 'edit'])->name('inventory.edit'); // <-- TAMBAHKAN INI
    Route::get('/inventory/{id}', [ProductController::class, 'show'])->name('inventory.detail');
    Route::put('/inventory/{id}', [ProductController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [ProductController::class, 'destroy'])->name('inventory.destroy');

    // ================================
    // C. ANALYTICS
    // ================================
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData'])->name('analytics.chart');

    // ================================
    // D. AI COPILOT
    // ================================
    Route::get('/ai-copilot', [AICopilotController::class, 'index'])->name('ai.copilot');

    // ================================
    // E. POS CASHIER
    // ================================
    Route::get('/pos-cashier', [POSCashierController::class, 'index'])->name('pos.cashier');
    Route::post('/pos/add', [POSCashierController::class, 'addToCart'])->name('pos.add');
    Route::post('/pos/update', [POSCashierController::class, 'updateCart'])->name('pos.update');
    Route::delete('/pos/remove/{id}', [POSCashierController::class, 'removeFromCart'])->name('pos.remove');
    Route::post('/pos/clear', [POSCashierController::class, 'clearCart'])->name('pos.clear');
    Route::post('/pos/checkout', [POSCashierController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/save', [POSCashierController::class, 'checkout'])->name('pos.save');

    // ================================
    // F. PENGATURAN
    // ================================
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::put('/pengaturan/update', [PengaturanController::class, 'updateProfile'])->name('pengaturan.update');
    Route::put('/pengaturan/password', [PengaturanController::class, 'updatePassword'])->name('pengaturan.password');


    // ================================
    // G. GENERATE KODE BARANG (AJAX)
    // ================================
    Route::get('/inventory/generate-code', [ProductController::class, 'generateCodeAjax'])->name('inventory.generate');

});