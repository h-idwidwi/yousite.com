<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangeLogsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\LogRequestController;

Route::get('/report', [\App\Http\Controllers\ReportController::class, 'report']);
Route::post('/hooks/git', [GitController::class, 'startUpdate']);
Route::middleware(['CheckPermission', 'MakeLog'])->group(function () {
    Route::prefix('ref')->group(function () {
        Route::prefix('log')->group(function() {
            Route::get('{id}/restore', [ChangeLogsController::class, 'restoreEntity']);
            Route::get('/request', [LogRequestController::class, 'getLogs'])->name('getLogs');
            Route::get('/request/{id}', [LogRequestController::class, 'getLog'])->name('getLog');
            Route::delete('/request/{id}', [LogRequestController::class, 'deleteLog'])->name('deleteLog');
        });
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'getUsers'])->name('getUsers');
            Route::get('{id}/role', [UserController::class, 'getUserRoles'])->name('getUserRoles');
            Route::post('{id}/role', [UserController::class, 'giveUserRoles'])->name('giveUserRoles');
            Route::delete('{id}/role/{r_id}', [UserController::class, 'hardDeleteRole'])->name('userHardDeleteRole');
            Route::delete('{id}/role/{r_id}/soft', [UserController::class, 'softDeleteRole'])->name('userSoftDeleteRole');
            Route::post('{id}/role/{r_id}/restore', [UserController::class, 'restoreDeletedRole'])->name('userRestoreDeletedRole');
            Route::post('{id}/update', [UserController::class, 'updateUser'])->name('updateUser');
            Route::post('{id}/story', [ChangeLogsController::class, 'getUserLogs'])->name('getUserLogs');
        });

        Route::prefix('policy')->group(function () {
            Route::prefix('role')->group(function () {
                Route::get('/', [RoleController::class, 'getRoles'])->name('getRoles');
                Route::get('/{id}', [RoleController::class, 'getTargetRole'])->name('getTargetRole');
                Route::post('/', [RoleController::class, 'createRole'])->name('createRole');
                Route::put('/{id}', [RoleController::class, 'updateRole'])->name('updateRole');
                Route::delete('/{id}', [RoleController::class, 'hardDeleteRole'])->name('hardDeleteRole');
                Route::delete('/{id}/soft', [RoleController::class, 'softDeleteRole'])->name('softDeleteRole');
                Route::post('/{id}/restore', [RoleController::class, 'restoreDeletedRole'])->name('restoreDeletedRole');
                Route::post('/{id}/story', [ChangeLogsController::class, 'getRoleLogs'])->name('getRoleLogs');
            });

            Route::prefix('permission')->group(function () {
                Route::get('/', [PermissionController::class, 'getPermissions'])->name('getPermissions');
                Route::get('/{id}', [PermissionController::class, 'getTargetPermission'])->name('getTargetPermission');
                Route::post('/', [PermissionController::class, 'createPermission'])->name('createPermission');
                Route::put('/{id}', [PermissionController::class, 'updatePermission'])->name('updatePermission');
                Route::delete('/{id}', [PermissionController::class, 'hardDeletePermission'])->name('hardDeletePermission');
                Route::delete('/{id}/soft', [PermissionController::class, 'softDeletePermission'])->name('softDeletePermission');
                Route::post('/{id}/restore', [PermissionController::class, 'restoreDeletedPermission'])->name('restoreDeletedPermission');
                Route::post('/{id}/story', [ChangeLogsController::class, 'getPermissionLogs'])->name('getPermissionLogs');
            });
        });
    });
});

Route::prefix('auth')->group(function() {
    Route::middleware('MakeLog')->group(function (){
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('/2fa/verify', [AuthController::class, 'confirm2FACode'])->middleware('IsExpiry');
        Route::post('/2fa/resend', [AuthController::class, 'resendCode']);
        Route::middleware('AuthCheck')->group(function (){
            Route::post('register', [AuthController::class, 'register'])->name('register');
        });
        Route::middleware(['auth:api', 'IsExpiry'])->group(function () {
            Route::get('me', [UserController::class, 'me'])->name('me');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('logout_all', [AuthController::class, 'logout_all'])->name('logout_all');
            Route::get('tokens', [UserController::class, 'tokens'])->name('tokens');
        });
    });
});
