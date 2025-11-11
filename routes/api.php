<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\BankAccController;
use App\Http\Controllers\StreetController;
use App\Http\Controllers\TaxPayerController;
use App\Http\Controllers\TaxpayerTypeController;
use App\Http\Controllers\BillregisterController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\TblPropTypeController;
use App\Http\Controllers\TblPropUseIDController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('user')->middleware(['permissions:profile_all'])->group(function () {
        Route::get('/profile', [AuthUserController::class, 'userProfile'])->name('user.userProfile');
        Route::put('/profile', [AuthUserController::class, 'updateUserProfile'])->name('user.updateProfile');
        Route::post('/change-password', [AuthUserController::class, 'changePassword'])->name('user.changePassword');
    });


    Route::prefix('admin')->group(function () {
        Route::resource('streets', StreetController::class)->middleware(['permissions:dashboard']);
        Route::resource('bank-accounts', BankAccController::class)->middleware(['permissions:dashboard']);
        Route::resource('tbl-prop-types', TblPropTypeController::class)->middleware(['permissions:dashboard']);
        Route::resource('tbl-prop-use-ids', TblPropUseIDController::class)->middleware(['permissions:dashboard']);
        Route::resource('tax-payer-types', TaxpayerTypeController::class)->middleware(['permissions:dashboard']);
        Route::resource('tax-rates', TaxRateController::class)->middleware(['permissions:dashboard']);
        Route::resource('tax-payers', TaxPayerController::class)->middleware(['permissions:dashboard']);
        Route::get('tax-payers/client-no/{id}', [TaxPayerController::class, 'clientNoShow'])
            ->name('tax-payers.client.show')
            ->middleware(['permissions:dashboard']);
        Route::get('tax-payer-list', [TaxPayerController::class, 'taxPayer'])->middleware(['permissions:dashboard']);
    });


   
});


    Route::prefix('bill')->group(function () {
        Route::post('/general', [BillregisterController::class, 'billgenerate']);
        Route::post('/govt',    [BillregisterController::class, 'govtbillgenerate']);
        Route::post('/single',  [BillregisterController::class, 'singlebillgenerate']);
    });