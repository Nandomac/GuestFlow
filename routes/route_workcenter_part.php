<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkcenterPartController;
use App\Http\Controllers\WorkcenterPartCharacteristicController;

// Characteristics
Route::get('/workcenter-part', [WorkcenterPartController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.index');

Route::get('/workcenter-part/create', [WorkcenterPartController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.create');

Route::get('/workcenter-part/create-form/{id?}', [WorkcenterPartController::class, 'createFrom'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.create-form');

Route::post('/workcenter-part/store', [WorkcenterPartController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.store');

Route::get('/workcenter-part/edit/{id?}', [WorkcenterPartController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.edit');

Route::put('/workcenter-part/update/{id}', [WorkcenterPartController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.update');

Route::delete('/workcenter-part/destroy/{id?}', [WorkcenterPartController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.destroy');

Route::get('/workcenter-part/list', [WorkcenterPartController::class, 'list'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.list');

Route::get('/workcenter-part/group-details/{workcenter_part_id}/{characteristic_group_id?}', [WorkcenterPartController::class, 'getDetailWorkcenterPartGroup'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.group-details');

Route::put('/workcenter-part-characteristic/update/{id?}', [WorkcenterPartCharacteristicController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.update');

Route::delete('/workcenter-part-characteristic/destroy/{id?}', [WorkcenterPartCharacteristicController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.destroy');

Route::delete('/workcenter-part-characteristic/destroy-group/{workcenter_part_id?}/{characteristic_group_id?}', [WorkcenterPartCharacteristicController::class, 'destroyGrup'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.destroyGroup');

Route::get('/workcenter/part/pdf/{id?}', [WorkcenterPartController::class, 'generatePDF'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.pdf');

Route::get('/workcenter-part-characteristic/create/{workcenter_part_id?}/{partno_id?}/{characteristic_group_id?}/{characteristic_group?}', [WorkcenterPartCharacteristicController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.create');

Route::post('/workcenter-part-characteristic/store', [WorkcenterPartCharacteristicController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.store');

Route::get('/workcenter-part-characteristic/searchAvailableSetupCharacteristics/{workcenter_part_id?}/{searchCharacteristic?}', [WorkcenterPartCharacteristicController::class, 'searchAvailableSetupCharacteristics'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part-characteristic.searchAvailableSetupCharacteristics');

Route::post('/workcenter-part/update-group-order', [WorkcenterPartController::class, 'updateGroupOrder'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.updateGroupOrder');

Route::post('/workcenter-part/update-characteristic-order', [WorkcenterPartController::class, 'updateCharacteristicOrder'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter-part.updateCharacteristicOrder');


