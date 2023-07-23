<?php

use Celysium\Permission\Controllers\PermissionController;
use Celysium\Permission\Controllers\RoleController;
use Celysium\Permission\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {

    Route::name('permission')->middleware(config('permission.route_middlewares'))->group(function () {

        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class)->except(['store', 'destroy']);

        Route::name('users')->prefix('users')->group(function () {
            Route::post('/{id}/roles', [UserController::class, 'roles'])->name('roles');
            Route::post('/{id}/permissions', [UserController::class, 'permissions'])->name('permissions');
        });
    });
});