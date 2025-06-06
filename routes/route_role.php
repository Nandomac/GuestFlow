<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::get('/security/role', [RoleController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('role.index');

Route::get('/security/role/create', [RoleController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('role.create');

Route::post('/security/role/store', [RoleController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('role.store');

Route::get('/security/role/edit/{id}', [RoleController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('role.edit');

Route::put('/security/role/update/{id}', [RoleController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('role.update');

Route::get('/security/role/destroy/{id}', [RoleController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('role.destroy');
