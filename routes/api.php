<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;


Route::post('signin', [AuthController::class, 'signin']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('signout', [AuthController::class, 'signout']);



Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
Route::apiResource('orders', OrderController::class)->except(['create', 'edit']);
Route::post('orders/{order}/place', [OrderController::class, 'place'])->name('orders.place');
Route::post('orders/{order}/fake-payment', [OrderController::class, 'fakePayment'])->name('orders.fake-payment');
Route::post('stocks/increase', [StockController::class, 'increase'])->name('stocks.increase');
