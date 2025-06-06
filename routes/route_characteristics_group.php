<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CharacteristicGroupController;

// Characteristics
Route::get('/characteristic-group', [CharacteristicGroupController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.index');

Route::get('/characteristic-group/create', [CharacteristicGroupController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.create');

Route::post('/characteristic-group/store', [CharacteristicGroupController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.store');

Route::get('/characteristic-group/edit/{id?}', [CharacteristicGroupController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.edit');

Route::put('/characteristic-group/update/{id}', [CharacteristicGroupController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.update');

Route::delete('/characteristic-group/destroy/{id?}', [CharacteristicGroupController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.destroy');

Route::get('/characteristic-group/list', [CharacteristicGroupController::class, 'list'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.list');

Route::get('/characteristic-group/search/{searchGroup?}', [CharacteristicGroupController::class, 'searchGroupCharacteristics'])
    ->middleware(['auth', 'verified'])
    ->name('characteristic-group.searchCharacteristics');

