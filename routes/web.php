<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Ui\AuthController;
use App\Http\Controllers\Ui\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('tripay/callback', [CallbackController::class, 'callbackTripay']);
Route::post('digiflazz/callback', [CallbackController::class, 'callbackDigiflazz']);

// WebBase

// Public
Route::get('/', function () {
    return view('welcome');
});

// Price List
Route::prefix('price-list')->group(function () {
    Route::get('prabayar', [ProductController::class, 'priceListPrabayar'])->name('price.list.prabayar');
});

Route::prefix('histories')->group(function () {
    // Prabayar
    Route::prefix('transaction/prabayar')->group(function () {
        // Login
        Route::get('', [TransactionController::class, 'historyPrabayar'])->name('history.transaction.prabayar');
        Route::post('', [TransactionController::class, 'historyPrabayarAction']);

        // Get Data
        Route::get('{token}', [TransactionController::class, 'getHistoryPrabayar'])->name('get.history.transaction');
    });
});

Route::get('get-provider', [ProductController::class, 'getProvider'])->name('prabayar.getProvider');
Route::get('get-type', [ProductController::class, 'getType'])->name('prabayar.getType');
Route::get('get-services', [ProductController::class, 'getServices'])->name('prabayar.getServices');

// Auth Admin/web report (To do)
// Route::prefix('auth')->group(function () {
//     Route::get('login', [AuthController::class, 'login'])->name('login');
//     Route::post('login', [AuthController::class, 'loginStore'])->name('login.store');
// });

// Route::middleware('auth')->group(function () {
//     Route::get('dashboard', DashboardController::class)->name('dashboard');

//     Route::post('logout', [AuthController::class, 'logout'])->name('logout');
// });
