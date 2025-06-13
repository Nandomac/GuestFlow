<?php

use App\Http\Controllers\CharacteristicController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkcenterStructureController;
use App\Http\Controllers\WorkcenterDowntimeEmailController;

Route::get('/', function () {
    return view('dash');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', function () {
    return view('dash');
})->middleware(['auth', 'verified'])->name('dashboard');


// Operation
require __DIR__ . '/route_guestflow.php';

// Oracle
require __DIR__ . '/route_oracle.php';

// Security
require __DIR__ . '/route_role.php';
require __DIR__ . '/auth.php';
