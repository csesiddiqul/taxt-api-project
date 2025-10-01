<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthUserController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');

    Route::post('/verify-phone', 'verifyPhone');
    Route::post('/resend-verify-phone', 'resendVerifyPhone');

    Route::post("/forgot-password", 'forgotPassword');
    Route::post("/resend-password-code", 'resendPasswordCode');
    Route::post("/reset-password", 'resetPassword');

    Route::get('/send-sms', 'sendSms');
});
