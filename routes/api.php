<?php

use Celysium\ACL\Controllers\PermissionController;
use Celysium\ACL\Controllers\RoleController;
use Celysium\ACL\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {

    Route::name('acl')->middleware(config('acl.route_middlewares'))->group(function () {

        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class);

        Route::name('users')->prefix('users')->group(function () {

            Route::post('/{id}/roles', [UserController::class, 'roles'])->name('roles');
            Route::post('/{id}/permissions', [UserController::class, 'permissions'])->name('permissions');
        });
    });
});