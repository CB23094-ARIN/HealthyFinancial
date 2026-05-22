<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [BudgetController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [BudgetController::class, 'showProfile'])->name('profile.edit');
    Route::patch('/profile', [BudgetController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [BudgetController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/transaction', [BudgetController::class, 'storeTransaction'])->name('transaction.store');
    Route::get('/can-afford', [BudgetController::class, 'showCanAffordForm'])->name('can-afford.form');
    Route::post('/can-afford', [BudgetController::class, 'canAfford'])->name('can-afford.check');
    Route::get('/scan-receipt', [BudgetController::class, 'showScanReceipt'])->name('scan-receipt.form');
    Route::post('/scan-receipt', [BudgetController::class, 'uploadReceipt'])->name('scan-receipt.upload');
    Route::get('/leaderboard', [BudgetController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/transactions', [BudgetController::class, 'transactions'])->name('transactions');
});
