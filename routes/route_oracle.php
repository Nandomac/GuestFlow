<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OracleController;

Route::prefix('ifs')->group(function () {
    Route::get('/workcenters', [OracleController::class, 'getWorkcenterToSync'])->name('ifs.workcenters');
    Route::get('/downtime-causes', [OracleController::class, 'getDowntimeCauses'])->name('ifs.downtime-causes');
    Route::get('/locations/{workcenterCode}', [OracleController::class, 'getWorkcenterLocations'])->name('ifs.locations');
    Route::get('/inventory-parts/{partnoId?}/{contract?}', [OracleController::class, 'getInventoryParts'])->name('ifs.inventory-parts');
});
