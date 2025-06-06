<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopOrderController;

Route::post('/shop-order/start-production', [ShopOrderController::class, 'startProduction'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.startProduction');

Route::post('/shop-order/finish-production', [ShopOrderController::class, 'finishProduction'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.finishProduction');

Route::get('/shop-order/downtime-reasons', [ShopOrderController::class, 'getDowntimeReasons'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.getDowntimeReasons');

Route::post('/shop-order/downtime', [ShopOrderController::class, 'recordDowntime'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.recordDowntime');

Route::get('/shop-order/getfinish-downtime', [ShopOrderController::class, 'getFinishDowntime'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.getFinishDowntime');

Route::post('/shop-order/finish-downtime', [ShopOrderController::class, 'recordFinishDowntime'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.recordFinishDowntime');

Route::get('/getPnoComponentHistory', [ShopOrderController::class, 'getPnoComponentHistory'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.getPnoComponentHistory');

Route::post('/shop_order/issue-material', [ShopOrderController::class, 'issueMaterial'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.issueMaterial');

Route::get ('/shop_order/get-scrap-causes', [ShopOrderController::class, 'getScrapCauses'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.getScrapCauses');

Route::post('/shop_order/report-scrap-operation', [ShopOrderController::class, 'reportScrapOperation'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.reportScrapOperation');

Route::post('/shop_order/report-scrap-component', [ShopOrderController::class, 'reportScrapComponent'])
    ->middleware(['auth', 'verified'])
    ->name('shoporder.reportScrapComponent');