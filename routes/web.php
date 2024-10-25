<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::post('telegram/webhook', [TelegramBotController::class, 'webhook']);
Route::post('tripay/callback', [CallbackController::class, 'callbackTripay']);
