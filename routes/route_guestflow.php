<?php

use Illuminate\Support\Facades\Route;

Route::get('/flow', function () {
    return view('operation.flow');
})->middleware(['auth', 'verified'])->name('flow');
