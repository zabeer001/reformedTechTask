<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::get('/signin', 'signin');
        Route::post('/register', 'register');
        Route::post('/refresh', 'refresh');
        Route::post('/signout', 'signout');
    });
