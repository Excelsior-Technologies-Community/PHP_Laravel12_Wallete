<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Display welcome page
Route::get('/', function () {
    return view('welcome');
});

// Display dashboard page for authenticated and verified users
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile management routes (only for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Show profile edit form
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Update profile information
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Delete user account
});

// Wallet routes (only for authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index'); // Show wallet page
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit'); // Handle deposit request
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw'); // Handle withdraw request
});

// Authentication routes (login, register, logout, etc.)
require __DIR__.'/auth.php';
