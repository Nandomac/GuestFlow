<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackPrintController;

Route::get('/backprint', [BackPrintController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('backprint.index');

Route::get('/backprint/list', [BackPrintController::class, 'list'])
    ->middleware(['auth', 'verified'])
    ->name('backprint.list');

Route::post('/backprint/update-dpi', [BackPrintController::class, 'updateDpi'])
->middleware(['auth', 'verified'])
->name('backprint.updateDpi');