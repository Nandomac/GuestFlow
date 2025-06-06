<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserAPIController;

Route::middleware('auth:sanctum')->group( function () {
    //Route::apiResource('users', UserAPIController::class);
    Route::get('/users', [UserAPIController::class, 'index'])
    ->name('api.users.index');
    Route::get('/users/{id}', [UserAPIController::class, 'show'])
    ->name('api.users.show');
    Route::post('/users', [UserAPIController::class, 'store'])
    ->name('api.users.store');
    Route::put('/users/{id}', [UserAPIController::class, 'update'])
    ->name('api.users.update');
    Route::delete('/users/{id}', [UserAPIController::class, 'destroy'])
    ->name('api.users.delete');
    Route::put('/users/{id}/password', [UserAPIController::class, 'updateUserPassword'])
    ->name('api.users.updateUserPassword');
    Route::get('/users/{id}/roles', [UserAPIController::class, 'getUserRoles'])
    ->name('api.users.roles.getUserRoles');
    Route::put('/users/{id}/roles', [UserAPIController::class, 'updateUserRoles'])
    ->name('api.users.roles.updateUserRoles');
    Route::delete('/users/{id}/roles/{role}', [UserAPIController::class, 'removeUserRoles'])
    ->name('api.users.roles.removeUserRoles');
});
