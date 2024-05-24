<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth.check')->group(function (){
        Route::post('register', [AuthController::class, 'register'])->name('register');
    });

    Route::middleware(['auth:api', 'isExpiry'])->group(function () {
        Route::get('me', [UserController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout_all', [AuthController::class, 'logout_all'])->name('logout_all');
        Route::get('tokens', [UserController::class, 'tokens'])->name('tokens');
    });
});





