<?php

use Celysium\ACL\Controllers\PermissionController;
use Celysium\ACL\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('api')->group(function () {

    Route::name('acl')->middleware(config('acl.route_middlewares'))->group(function () {

        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class);
    });
});