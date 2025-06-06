<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CharacteristicController;

// Characteristics
Route::get('/characteristic', [CharacteristicController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.index');

Route::get('/characteristic/create', [CharacteristicController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.create');

Route::post('/characteristic/store', [CharacteristicController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.store');

Route::get('/characteristic/edit/{id?}', [CharacteristicController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.edit');

Route::put('/characteristic/update/{id}', [CharacteristicController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.update');

Route::delete('/characteristic/destroy/{id?}', [CharacteristicController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.destroy');

Route::get('/characteristic/list', [CharacteristicController::class, 'list'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic.list');
