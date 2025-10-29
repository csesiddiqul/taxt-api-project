<?php

use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\BankAccController;
use App\Http\Controllers\CustomizeRoleController;
use App\Http\Controllers\StreetController;
use App\Http\Controllers\TaxPayerController;
use App\Http\Controllers\TaxpayerTypeController;
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


    Route::prefix('web')->group(function () {
        // Permission-related routes
        Route::middleware(['permissions:permission_all'])->group(function () {
            Route::post('/assign-role-has-permissions', [AuthUserController::class, 'assignPermissionsToRole']);
            Route::post('/remove-role-has-permissions', [AuthUserController::class, 'removeRoleHasPermission']);
            Route::post('/create-permissions', [AuthUserController::class, 'createPermission']);
            Route::post('/permissions', [CustomizeRoleController::class, 'storePermission']);
            Route::get('/permissions-all', [CustomizeRoleController::class, 'permissionsAll']);
        });
        // Role-related routes
        Route::middleware(['permissions:role_all'])->group(function () {
            Route::resource('/roles', CustomizeRoleController::class);
        });
    });

    Route::prefix('admin')->group(function () {
        Route::resource('streets', StreetController::class)->middleware(middleware: ['permissions:dashboard']);
        Route::get('street/export', [StreetController::class, 'exportExcel'])->middleware(middleware: ['permissions:dashboard']);
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
