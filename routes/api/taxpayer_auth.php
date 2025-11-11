<?php

use App\Http\API\TaxPayerAuthController;
use Illuminate\Support\Facades\Route;

Route::controller(TaxPayerAuthController::class)->group(function () {
    Route::post('/client/register', 'register');
    Route::post('/client/login', 'login');
    Route::post('/client/verify-phone', 'verifyPhone');
    Route::post('/client/resend-verify-phone', 'resendVerifyPhone');
    Route::post('/client/forgot-password', 'forgotPassword');
    Route::post('/client/reset-password', 'resetPassword');

    Route::middleware('auth:sanctum')->post('/client/logout', 'logout');
});
