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



require __DIR__ . '/route_workcenter_structure.php';
require __DIR__ . '/route_workcenter_downtime_email.php';
require __DIR__ . '/route_characteristic.php';
require __DIR__ . '/route_characteristics_group.php';
require __DIR__ . '/route_workcenter_part.php';
require __DIR__ . '/route_oracle.php';
require __DIR__ . '/route_backprint.php';
require __DIR__ . '/route_production_area.php';
require __DIR__ . '/route_shoporder.php';


// Exemplo
require __DIR__ . '/route_role.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
