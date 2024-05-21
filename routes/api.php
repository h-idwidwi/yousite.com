<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsExpiry;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleAndPermissionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('CheckPermission')->group(function () {
    Route::prefix('ref')->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'getUsers']);
            Route::get('{id}/role', [UserController::class, 'getUserRoles']);
            Route::post('{id}/role', [UserController::class, 'giveUserRoles']);
            Route::delete('{id}/role/{role_id}', [UserController::class, 'hardDeleteRole']);
            Route::delete('{id}/role/{role_id}/soft', [UserController::class, 'softDeleteRole']);
            Route::post('{id}/role/{role_id}/restore', [UserController::class, 'restoreDeletedRole']);
        });

        Route::prefix('policy')->group(function () {
            Route::get('role', [RoleController::class, 'getRoles']);
            Route::get('role/{id}', [RoleController::class, 'getTargetRole']);
            Route::post('role', [RoleController::class, 'createRole']);
            Route::put('role/{id}', [RoleController::class, 'updateRole']);
            Route::delete('role/{id}', [RoleController::class, 'hardDeleteRole']);
            Route::delete('role/{id}/soft', [RoleController::class, 'softDeleteRole']);
            Route::post('role/{id}/restore', [RoleController::class, 'restoreDeletedRole']);



            Route::get('permission', [PermissionController::class, 'getPermissions']);
            Route::get('permission/{id}', [PermissionController::class, 'getTargetPermission']);
            Route::post('permission', [PermissionController::class, 'createPermission']);
            Route::put('permission/{id}', [PermissionController::class, 'updatePermission']);
            Route::delete('permission/{id}', [PermissionController::class, 'hardDeletePermission']);
            Route::delete('permission/{id}/soft', [PermissionController::class, 'softDeletePermission']);
            Route::post('permission/{id}/restore', [PermissionController::class, 'restoreDeletedPermission']);



            Route::get('role/{id}/permission', [RoleAndPermissionController::class, 'getRolePermission']);
            Route::get('role/{id}/permission/{permission_id}', [RoleAndPermissionController::class, 'addRolePermission']);
            Route::delete('role/{id}/permission/{permission_id}', [RoleAndPermissionController::class, 'hardDeleteRolePermission']);
            Route::delete('role/{id}/permission/{permission_id}/soft', [RoleAndPermissionController::class, 'softDeleteRolePermission']);
            Route::post('role/{id}/permission/{permission_id}/restore', [RoleAndPermissionController::class, 'restoreDeletedRolePermission']);
        });
    });
});

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [RegisterController::class, 'register'])->name('register');
    Route::middleware(['auth:api', 'IsExpiry'])->group(function () {
        Route::get('me', [UserController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout_all', [AuthController::class, 'logout_all'])->name('logout_all');
        Route::get('tokens', [UserController::class, 'tokens'])->name('tokens');
    });
});
