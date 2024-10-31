<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Ui\AuthController;
use App\Http\Controllers\Ui\DashboardController;
use Illuminate\Support\Facades\Route;

// Route::post('telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('tripay/callback', [CallbackController::class, 'callbackTripay']);
Route::post('digiflazz/callback', [CallbackController::class, 'callbackDigiflazz']);

// WebBase

// Public
Route::get('/', function () {
    return view('welcome');
});

Route::get('price-list', [ProductController::class, 'list'])->name('products.list');
Route::prefix('histories')->group(function () {
    Route::prefix('transaction')->group(function () {
        // Login
        Route::get('login', [TransactionController::class, 'loginHistories'])->name('histories.transaction.login');
        Route::post('login', [TransactionController::class, 'loginHistoriesAction']);

        // Get Data
        Route::get('{token}', [TransactionController::class, 'histories'])->name('histories.transaction');
    });
});

Route::get('get-provider', [ProductController::class, 'getProvider'])->name('prabayar.getProvider');
Route::get('get-type', [ProductController::class, 'getType'])->name('prabayar.getType');
Route::get('get-services', [ProductController::class, 'getServices'])->name('prabayar.getServices');

// Auth Admin (To do)
Route::prefix('auth')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'loginStore'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
