<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleAreaController;
use App\Http\Controllers\UserDirectoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'destroy']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', RoleAreaController::class);
        Route::get('/students', [UserDirectoryController::class, 'students']);
        Route::get('/teachers', [UserDirectoryController::class, 'teachers']);
    });
    Route::get('/teacher', RoleAreaController::class)->middleware('role:teacher');
    Route::get('/student', RoleAreaController::class)->middleware('role:student');
});
