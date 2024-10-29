<?php

use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\DigiflazzController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('verifyToken')->group(function () {

    Route::prefix('v1')->group(function () {
        Route::get('payment-method/{provider}', [PaymentMethodController::class, 'getPaymentProvider']);
        Route::get('get-products', [DigiflazzController::class, 'getProducts']);
        Route::get('update-products', [DigiflazzController::class, 'updateProducts']);

        // Users
        Route::prefix('users')->group(function () {
            Route::post('register', [UserApiController::class, 'register']);
            Route::post('check', [UserApiController::class, 'check']);
            Route::post('saldo', [UserApiController::class, 'saldo']);
            Route::post('deposit', [UserApiController::class, 'deposit']);
        });

        // Transaction
        Route::prefix('transaction')->group(function () {
            Route::post('create', [TransactionController::class, 'create']);
            Route::post('status', [TransactionController::class, 'status']);
            Route::post('histories', [TransactionController::class, 'histories']);
        });
    });
});
