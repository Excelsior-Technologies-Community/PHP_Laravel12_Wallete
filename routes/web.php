<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy'
    ])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Wallet Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/wallet', [
        WalletController::class,
        'index'
    ])->name('wallet.index');


    Route::post('/wallet/deposit', [
        WalletController::class,
        'deposit'
    ])->name('wallet.deposit');


    Route::post('/wallet/withdraw', [
        WalletController::class,
        'withdraw'
    ])->name('wallet.withdraw');


    /*
    |--------------------------------------------------------------------------
    | Wallet Transaction CSV Export
    |--------------------------------------------------------------------------
    */

    Route::get('/wallet/export', [
        WalletController::class,
        'export'
    ])->name('wallet.export');

    /*
    |--------------------------------------------------------------------------
    | P2P Wallet Transfer
    |--------------------------------------------------------------------------
    */

    Route::get('/wallet/transfer', [
        WalletController::class,
        'transferView'
    ])->name('wallet.transfer');

    Route::post('/wallet/transfer', [
        WalletController::class,
        'transferStore'
    ])->name('wallet.transfer.store');

    /*
    |--------------------------------------------------------------------------
    | Financial Analytics
    |--------------------------------------------------------------------------
    */

    Route::get('/wallet/analytics', [
        WalletController::class,
        'analyticsView'
    ])->name('wallet.analytics');

    Route::get('/wallet/analytics/data', [
        WalletController::class,
        'analyticsData'
    ])->name('wallet.analytics.data');

});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
