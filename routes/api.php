<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('signin', [AuthController::class, 'signin']);
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::post('signout', [AuthController::class, 'signout'])->middleware('auth:api');
});

Route::middleware('auth:api')->group(function () {
    Route::get('products/search', ProductSearchController::class);

    Route::apiResource('orders', OrderController::class)->except(['create', 'edit']);
    Route::post('orders/{order}/place', [OrderController::class, 'place'])->name('orders.place');
    Route::post('orders/{order}/fake-payment', [OrderController::class, 'fakePayment'])->name('orders.fake-payment');
});
