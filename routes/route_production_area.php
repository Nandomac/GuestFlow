<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductionAreaController;

Route::get('/production-area', [ProductionAreaController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.index');

Route::get('/production-area/details/{id}', [ProductionAreaController::class, 'productionAreaDetails'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.details');

Route::get('/production-area/getWorkcenterShopOrdersList/{id}/{contract}', [ProductionAreaController::class, 'getWorkcenterShopOrdersList'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.getWorkcenterShopOrdersList');

Route::get('/production-area/workcenter-shoporder/{id}/{operationId}', [ProductionAreaController::class, 'productionAreaWorkcenterShopOrder'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.productionAreaWorkcenterShopOrder');

Route::get('/production-area/workcenter-shoporder-details/{id}/{operationId}', [ProductionAreaController::class, 'productionAreaWorkcenterShopOrderDetails'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.productionAreaWorkcenterShopOrderDetails');

Route::post('/production-area/find-shop-order', [ProductionAreaController::class, 'productionAreaWorkcenterFindShopOrder'])
    ->middleware(['auth', 'verified'])
    ->name('production-area.productionAreaWorkcenterFindShopOrder');