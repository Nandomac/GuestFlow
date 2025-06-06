<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkcenterDowntimeEmailController;

Route::get('/workcenter/mail-list/{id?}/{workcenter_downtime_id?}', [WorkcenterDowntimeEmailController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter.mail-list');

Route::post('/workcenter/mail-list/save', [WorkcenterDowntimeEmailController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter.mail-list-save');

Route::delete('/workcenter/mail-list/remove/{id?}', [WorkcenterDowntimeEmailController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('workcenter.mail-list-remove');
