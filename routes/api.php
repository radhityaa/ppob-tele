<?php

use App\Http\Controllers\PaymentMethodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('payment-method/{provider}', [PaymentMethodController::class, 'getPaymentProvider']);
